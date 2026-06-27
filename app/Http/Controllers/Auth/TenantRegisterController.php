<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantRegisterController extends Controller
{
    /**
     * Register a new tenant and their owner account.
     */
    public function register(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'subdomain' => 'required|string|unique:tenants,subdomain|alpha_dash',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'name' => 'required|string',       // Owner name
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if (User::where('email', $request->email)->whereHas('roles', fn ($q) => $q->whereIn('name', ['owner', 'superadmin']))->exists()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['email' => ['This email is already registered as a business owner.']],
            ], 422);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['email' => ['This email is already registered as a customer at another salon. Please use a different email to register your business.']],
            ], 422);
        }

        // Database transaction ensures data integrity
        $result = DB::transaction(function () use ($request) {

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
                        'sun' => null,
                    ],
                    'timezone' => 'Asia/Kolkata',
                ],
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            $user->assignRole('owner');
            $user->sendEmailVerificationNotification();

            $freePlan = Plan::where('slug', 'free')->first();
            if ($freePlan) {
                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $freePlan->id,
                    'billing_cycle' => 'monthly',
                    'status' => 'trial',
                    'amount' => 0,
                    'starts_at' => now(),
                    'expires_at' => now()->addDays(14),
                ]);

                $tenant->update(['trial_ends_at' => now()->addDays(14)]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return ['tenant' => $tenant, 'user' => $user, 'token' => $token];
        });

        return response()->json([
            'message' => 'Salon registered successfully!',
            'token' => $result['token'],
            'tenant' => $result['tenant'],
            'user' => $result['user'],
        ], 201);
    }
}
