<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    /**
     * Login form — subdomain se tenant detect karo
     */
    public function showLogin($subdomain)
    {
        // Pehle se logged in customer — home pe bhejo
        if (Auth::check() && Auth::user()->hasRole('customer')) {
            return redirect()->route('customer.home', $subdomain);
        }

        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->firstOrFail();

        return view('customer.auth.login', compact('tenant'));
    }

    /**
     * Register form
     */
    public function showRegister($subdomain)
    {
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->firstOrFail();

        return view('customer.auth.register', compact('tenant'));
    }

    /**
     * Login process karo
     */
    public function login(Request $request, $subdomain)
    {
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->firstOrFail();

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Sirf is tenant ka customer
        $user = User::where('email', $request->email)
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email ya password galat hai.']);
        }

        if (! $user->hasRole('customer')) {
            return back()->withErrors(['email' => 'Ye account customer account nahi hai.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('customer.home', $subdomain);
    }

    /**
     * Register — naya customer banao
     */
    public function register(Request $request, $subdomain)
    {
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);
        $user->assignRole('customer');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.home', $subdomain)
            ->with('success', "Welcome, {$user->name}! Account ban gaya.");
    }

    /**
     * Logout
     */
    public function logout(Request $request, $subdomain)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login', $subdomain)
            ->with('success', 'Aap logout ho gaye hain.');
    }
}
