<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerAuthController extends Controller
{
    /**
     * Handle customer registration.
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(fn ($q) => $q->where('tenant_id', app('currentTenant')->id)),
            ],
            'phone' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        // Create the user associated with the current tenant
        $user = User::create([
            'tenant_id' => app('currentTenant')->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Assign the customer role to the user
        $user->assignRole('customer');

        // Create an authentication token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Customer registered successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'customer',
            ],
        ], 201);
    }
}
