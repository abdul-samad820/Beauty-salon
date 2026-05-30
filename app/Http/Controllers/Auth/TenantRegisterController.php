<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantRegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'subdomain' => 'required|string|unique:tenants,subdomain|alpha_dash',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'name' => 'required|string',       // Owner ka naam
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // DB Transaction — agar kuch fail ho toh dono rollback ho
        $result = DB::transaction(function () use ($request) {

            // Step 1: Tenant banao
            $tenant = Tenant::create([
                'name' => $request->business_name,
                'slug' => Str::slug($request->subdomain),
                'subdomain' => $request->subdomain,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'plan' => 'free',
                'status' => 'active',
                'settings' => [
                    'working_hours' => [
                        'mon' => '09:00-20:00',
                        'tue' => '09:00-20:00',
                        'wed' => '09:00-20:00',
                        'thu' => '09:00-20:00',
                        'fri' => '09:00-20:00',
                        'sat' => '09:00-20:00',
                        'sun' => null, // Sunday band
                    ],
                    'timezone' => 'Asia/Kolkata',
                ],
            ]);

            // Step 2: Owner user banao
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            // Step 3: Owner role assign karo (Spatie)
            $user->assignRole('owner');

            // Step 4: Token banao
            $token = $user->createToken('auth_token')->plainTextToken;

            return ['tenant' => $tenant, 'user' => $user, 'token' => $token];
        });

        return response()->json([
            'message' => 'Parlour registered successfully!',
            'token' => $result['token'],
            'tenant' => $result['tenant'],
            'user' => $result['user'],
        ], 201);
    }
}
