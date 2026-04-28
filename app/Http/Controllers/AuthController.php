<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\LoginRequest;
use App\Mail\OtpMail;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->validated())) {
            return response()->json([
            'message' => 'Invalid credentials',
            ], 401);
        }

        $request->session()->regenerate();
        
        $user = Auth::user();
        
        // If user is not verified, send OTP and deny access
        if (!$user->otp_verified) {
            $this->generateAndSendOtp($request);
            
            return response()->json([
                'message' => 'Account not verified. Please check your email for verification code.',
                'user' => new UserResource($user),
                'requires_verification' => true
            ], 403);
        }

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
    public function generateAndSendOtp(Request $request)
    {
        $user = $request->user();
        
        // 1. Generate 6-digit code
        $otp = rand(100000, 999999);

        // 2. Hash it and save to DB with 10-minute expiry
        $user->update([
            'otp' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // 3. Dispatch to Queue (The Background Move)
        // This ensures the response is instant
        Mail::to($user->email)->queue(new OtpMail($otp));

        return response()->json([
            'message' => 'Verification code sent to your email',
            'expires_at' => $user->otp_expires_at
        ]);
    }
 public function verifyOtp(Request $request)
{
    $request->validate(['otp' => 'required|string|size:6']);
    $user = $request->user();

    // 1. Check Expiry
    if (now()->isAfter($user->otp_expires_at)) {
        return response()->json(['message' => 'Code expired.'], 422);
    }

    // 2. Check Hash match
    if (!Hash::check($request->otp, $user->otp)) {
        return response()->json(['message' => 'Invalid code.'], 422);
    }

    // 3. Success
    $user->update([
        'otp_verified' => true,
        'otp' => null,
        'otp_expires_at' => null,
    ]);

    return response()->json(['message' => 'Identity verified. Terminal unlocked.']);
}
    public function logout(Request $request)
    {


        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        
        // Generate and send OTP
        $otp = rand(100000, 999999);
        
        // Store pending registration in cache with 10-minute expiry
        $pendingUser = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'otp' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10)->timestamp,
        ];
        
        Cache::put("pending_registration:{$validated['email']}", $pendingUser, 600); // 10 minutes
        
        // Send OTP email
        Mail::to($validated['email'])->queue(new OtpMail($otp));

        return response()->json([
            'message' => 'Registration initiated. Please check your email for verification code.',
            'pending_email' => $validated['email'],
            'expires_at' => now()->addMinutes(10),
        ], 202);
    }
    
    public function generateAndSendOtpPublic(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        // Check if user exists in cache (pending registration)
        $pendingKey = "pending_registration:{$request->email}";
        $pendingUser = Cache::get($pendingKey);
        
        if (!$pendingUser) {
            return response()->json(['message' => 'Registration not found or expired.'], 404);
        }
        
        // 1. Generate 6-digit code
        $otp = rand(100000, 999999);

        // 2. Update OTP in cache with 10-minute expiry
        $pendingUser['otp'] = Hash::make($otp);
        $pendingUser['otp_expires_at'] = now()->addMinutes(10)->timestamp;
        Cache::put($pendingKey, $pendingUser, 600); // 10 minutes

        // 3. Dispatch to Queue (The Background Move)
        // This ensures the response is instant
        Mail::to($request->email)->queue(new OtpMail($otp));

        return response()->json([
            'message' => 'Verification code sent to your email',
            'expires_at' => now()->addMinutes(10)
        ]);
    }
    
    public function verifyOtpPublic(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        // Get pending registration from cache
        $pendingKey = "pending_registration:{$request->email}";
        $pendingUser = Cache::get($pendingKey);

        if (!$pendingUser) {
            return response()->json(['message' => 'Registration not found or expired.'], 422);
        }

        // Check OTP expiry
        if (now()->timestamp > $pendingUser['otp_expires_at']) {
            Cache::forget($pendingKey);
            return response()->json(['message' => 'Code expired.'], 422);
        }

        // Verify OTP
        if (!Hash::check($request->otp, $pendingUser['otp'])) {
            return response()->json(['message' => 'Invalid code.'], 422);
        }

        // Create the user in database
        $user = User::create([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'password' => $pendingUser['password'],
            'otp_verified' => true,
        ]);

        // Clean up cache
        Cache::forget($pendingKey);

        // Log the user in with session
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registration completed successfully!',
            'user' => new UserResource($user),
        ], 201);
    }
    
    public function completeRegistration(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        // Get pending registration from cache
        $pendingKey = "pending_registration:{$request->email}";
        $pendingUser = Cache::get($pendingKey);

        if (!$pendingUser) {
            return response()->json(['message' => 'Registration not found or expired.'], 422);
        }

        // Check OTP expiry
        if (now()->timestamp > $pendingUser['otp_expires_at']) {
            Cache::forget($pendingKey);
            return response()->json(['message' => 'Code expired.'], 422);
        }

        // Verify OTP
        if (!Hash::check($request->otp, $pendingUser['otp'])) {
            return response()->json(['message' => 'Invalid code.'], 422);
        }

        // Create the user in database
        $user = User::create([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'password' => $pendingUser['password'],
            'otp_verified' => true,
        ]);

        // Clean up cache
        Cache::forget($pendingKey);

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registration completed successfully!',
            'user' => new UserResource($user),
        ], 201);
    }
}
