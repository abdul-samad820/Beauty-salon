<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
                'message' => 'Email ya password galat hai.',
            ], 401);
        }

        $user = Auth::user();

        // Inactive user block karo
        if (! $user->is_active) {

            Auth::logout();

            return response()->json([
                'message' => 'Account inactive hai. Owner se contact karo.',
            ], 403);
        }

        // Token banao
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(), // Spatie se role
                'tenant_id' => $user->tenant_id,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        // Current token delete karo
        if ($request->user()?->currentAccessToken()) {

            $request->user()
                ->currentAccessToken()
                ->delete();

        }

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}
