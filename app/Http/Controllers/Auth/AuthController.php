<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $user = Auth::user();

        // Block inactive users
        if (! $user->is_active) {

            Auth::logout();

            return response()->json([
                'message' => 'Account is inactive. Please contact the owner.',
            ], 403);
        }

        // Create authentication token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(), // Spatie role
                'tenant_id' => $user->tenant_id,
            ],
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        // Delete the current access token
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
