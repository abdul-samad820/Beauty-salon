<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class CustomerAuthController extends Controller
{
    // ── Login Form ───────────────────────────────────────────────
    public function showLogin($subdomain)
    {
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->firstOrFail();

        if (Auth::guard('customer')->check()) {
            $user = Auth::guard('customer')->user();

            if ((int) $user->tenant_id === (int) $tenant->id) {
                return redirect()->route('customer.landing', $subdomain);
            }

            Auth::guard('customer')->logout();
        }

        return view('customer.auth.login', compact('tenant', 'subdomain'));
    }

    // ── Register Form ────────────────────────────────────────────
    public function showRegister($subdomain)
    {
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->firstOrFail();

        if (Auth::guard('customer')->check()) {
            $user = Auth::guard('customer')->user();
            if ((int) $user->tenant_id === (int) $tenant->id) {
                return redirect()->route('customer.landing', $subdomain);
            }
            Auth::guard('customer')->logout();
        }

        return view('customer.auth.register', compact('tenant', 'subdomain'));
    }

    // ── Login POST ───────────────────────────────────────────────
    public function login(Request $request, $subdomain)
    {
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->firstOrFail();

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            return back()->withErrors(['email' => 'Your account is currently inactive.']);
        }

        if (method_exists($user, 'hasRole') && ! $user->hasRole('customer')) {
            return back()->withErrors(['email' => 'This account is not registered as a customer.']);
        }

        Auth::guard('customer')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('customer.landing', $subdomain);
    }

    // ── Register POST ────────────────────────────────────────────
    public function register(Request $request, $subdomain)
    {
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->where(fn ($q) => $q->where('tenant_id', $tenant->id))],
            'phone' => 'required|string|max:20',
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        if (method_exists($user, 'assignRole')) {
            $role = Role::where('name', 'customer')
                ->where('guard_name', 'web')
                ->first();

            if (! $role) {
                $role = Role::firstOrCreate([
                    'name' => 'customer',
                    'guard_name' => 'web',
                ]);
            }

            $user->assignRole($role);
        }

        Auth::guard('customer')->login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.landing', $subdomain)
            ->with('success', "Welcome, {$user->name}! Your account has been created successfully.");
    }

    // ── Logout ───────────────────────────────────────────────────
    public function logout(Request $request, $subdomain)
    {
        Auth::guard('customer')->logout();

        $request->session()->forget('guard_customer');
        $request->session()->regenerateToken();

        return redirect()->route('customer.login', $subdomain)
            ->with('success', 'You have been successfully logged out.');
    }

    public function showForgotPassword($subdomain)
    {
        return view('customer.auth.forgot-password', compact('subdomain'));
    }

    public function sendResetLink(Request $request, $subdomain)
    {
        $request->validate(['email' => 'required|email']); // pehle validate

        $tenant = Tenant::where('subdomain', $subdomain)->firstOrFail();

        $status = PasswordBroker::broker('customers')->sendResetLink(
            array_merge($request->only('email'), ['tenant_id' => $tenant->id])
        );

        return back()->with('success', 'If an account exists with that email, a password reset link has been sent.');

    }

    public function showResetForm(Request $request, $subdomain, $token)
    {
        return view('customer.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request, $subdomain)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $tenant = Tenant::where('subdomain', $subdomain)->firstOrFail();

        $status = PasswordBroker::broker('customers')->reset(
            array_merge(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                ['tenant_id' => $tenant->id]
            ),
            function ($user, $password) {
                $user->forceFill(['password' => \Hash::make($password)])
                    ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === PasswordBroker::PASSWORD_RESET
            ? redirect()->route('customer.login', $subdomain)->with('success', 'Password reset successful!')
            : back()->withErrors(['email' => __($status)]);
    }
}
