<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{
    private const OTP_TTL          = 600; // 10 minutes
    private const MAX_OTP_ATTEMPTS = 5;

    /*
    |--------------------------------------------------------------------------
    | REGISTER (STEP 1: STORE TEMP DATA + SEND OTP)
    |--------------------------------------------------------------------------
    */
    public function register(RegisterRequest $request): JsonResponse
    {
        $email = strtolower($request->email);

        // Reject if user already fully registered
        if (User::where('email', $email)->exists()) {
            return response()->json([
                'message' => 'Email already registered.',
            ], 409);
        }

        $key = "pending_registration:{$email}";

        if (Redis::exists($key)) {
            return response()->json([
                'message' => 'Registration already in progress. Check your email or request a new OTP.',
            ], 409);
        }

        $otp = $this->generateOtp();

        Redis::setex($key, self::OTP_TTL, json_encode([
            'name'     => $request->name,
            'email'    => $email,
            'password' => Hash::make($request->password),
            'otp'      => $otp,
            'attempts' => 0,
        ]));

        Mail::to($email)->queue(new OtpMail($otp));

        return response()->json([
            'message' => 'OTP sent to your email. Valid for 10 minutes.',
        ], 202);
    }

    /*
    |--------------------------------------------------------------------------
    | COMPLETE REGISTRATION (VERIFY OTP + CREATE USER + AUTO LOGIN)
    |--------------------------------------------------------------------------
    */
    public function completeRegistration(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower($request->email);
        $key   = "pending_registration:{$email}";

        $pending = $this->getPendingRegistration($key);

        if (!$pending) {
            return response()->json([
                'message' => 'Registration expired or not found. Please register again.',
            ], 422);
        }

        // Check attempt limit before verifying
        if ($pending['attempts'] >= self::MAX_OTP_ATTEMPTS) {
            Redis::del($key);
            return response()->json([
                'message' => 'Too many failed attempts. Please register again.',
            ], 429);
        }

        if (!hash_equals($pending['otp'], $request->otp)) {
            $remaining = $this->incrementOtpAttempts($key, $pending);
            return response()->json([
                'message'            => 'Invalid OTP.',
                'attempts_remaining' => max(0, self::MAX_OTP_ATTEMPTS - $remaining),
            ], 422);
        }

        // Guard against race condition — check again right before creation
        if (User::where('email', $email)->exists()) {
            Redis::del($key);
            return response()->json([
                'message' => 'An account with this email already exists.',
            ], 409);
        }

        $user = User::create([
            'name'         => $pending['name'],
            'email'        => $email,
            'password'     => $pending['password'],
            'otp_verified' => true,
        ]);

        Redis::del($key);

        auth()->login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Account created and logged in successfully.',
            'user'    => new UserResource($user),
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', strtolower($request->email))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (!$user->otp_verified) {
            return response()->json([
                'message' => 'Account not verified. Please verify your email first.',
            ], 403);
        }

        auth()->login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout(Request $request): JsonResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CURRENT AUTH USER
    |--------------------------------------------------------------------------
    */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RESEND OTP (PUBLIC — during registration flow)
    |--------------------------------------------------------------------------
    */
    public function generateAndSendOtpPublic(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($request->email);
        $key   = "pending_registration:{$email}";

        $pending = $this->getPendingRegistration($key);

        if (!$pending) {
            return response()->json([
                'message' => 'Registration not found or expired. Please register again.',
            ], 404);
        }

        $otp = $this->generateOtp();

        $pending['otp']      = $otp;
        $pending['attempts'] = 0; // Reset attempts on resend

        // Preserve remaining TTL — do not give extra time on resend
        $ttl = max((int) Redis::ttl($key), 1);
        Redis::setex($key, $ttl, json_encode($pending));

        Mail::to($email)->queue(new OtpMail($otp));

        return response()->json([
            'message' => 'A new OTP has been sent to your email.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFY OTP (PUBLIC — standalone check without completing registration)
    |--------------------------------------------------------------------------
    */
    public function verifyOtpPublic(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower($request->email);
        $key   = "pending_registration:{$email}";

        $pending = $this->getPendingRegistration($key);

        if (!$pending) {
            return response()->json([
                'message' => 'Registration not found or expired. Please register again.',
            ], 404);
        }

        if ($pending['attempts'] >= self::MAX_OTP_ATTEMPTS) {
            Redis::del($key);
            return response()->json([
                'message' => 'Too many failed attempts. Please register again.',
            ], 429);
        }

        if (!hash_equals($pending['otp'], $request->otp)) {
            $remaining = $this->incrementOtpAttempts($key, $pending);
            return response()->json([
                'message'            => 'Invalid OTP.',
                'attempts_remaining' => max(0, self::MAX_OTP_ATTEMPTS - $remaining),
            ], 422);
        }

        return response()->json([
            'message' => 'OTP verified successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Retrieve and decode a pending registration from Redis.
     * Returns null if the key does not exist.
     */
    private function getPendingRegistration(string $key): ?array
    {
        $data = Redis::get($key);
        return $data ? json_decode($data, true) : null;
    }

    /**
     * Increment the OTP attempt counter while preserving TTL.
     * Returns the new attempt count.
     */
    private function incrementOtpAttempts(string $key, array $pending): int
    {
        $pending['attempts']++;
        $ttl = max((int) Redis::ttl($key), 1);
        Redis::setex($key, $ttl, json_encode($pending));
        return $pending['attempts'];
    }

    /**
     * Generate a cryptographically random 6-digit OTP.
     */
    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}