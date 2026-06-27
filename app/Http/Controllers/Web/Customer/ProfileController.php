<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * File: app/Http/Controllers/Web/Customer/ProfileController.php
 */
class ProfileController extends Controller
{
    /**
     * Display the customer profile page.
     */
    public function index($subdomain)
    {
        return view('customer.profile.index', [
            'user' => Auth::guard('customer')->user(),
            'tenant' => app('customerTenant'),
            'subdomain' => $subdomain,
        ]);
    }

    /**
     * Update the customer profile details.
     */
    public function update(Request $request, $subdomain)
    {
        $user = Auth::guard('customer')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($request->only('name', 'phone'));

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the customer password.
     */
    public function updatePassword(Request $request, $subdomain)
    {
        $user = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password provided is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
