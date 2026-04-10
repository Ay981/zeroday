<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = Auth::user()->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        // Instant Login: Issue a token immediately so they don't have to login after signing up
        $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201); // 201 = Created
    }
}
