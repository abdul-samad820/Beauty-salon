<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AuthWebController
 *
 * Centralized web state authentications and post-login redirection channels layer.
 */
class AuthWebController extends Controller
{
    /**
     * Show the luxury baseline login system form view layout interface.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Process web authentication validation parameters and dispatch strategic route redirects.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Access Denied: Invalid account credentials entered.']);
        }

        $user = Auth::user();

        // FIXED SEC-001: Enforce global platform activation checking bounds matrix safely
        if (! $user->is_active) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Access Denied: Invalid account credentials entered.']);
        }

        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    /**
     * Safely terminate web login tracking cookies and flush active runtime memory maps.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Notification: Session security scope terminated successfully.');
    }

    /**
     * Redirect users dynamically based on their validated administrative roles.
     */
    private function redirectByRole($user)
    {
        $route = $user->dashboardRouteName();

        if ($route) {
            return redirect()->route($route);
        }

        // Unknown role — secure fallback layer execution boundary logout
        Auth::logout();

        return redirect()->route('login')->withErrors([
            'email' => 'Access Denied: Your user account profile does not possess a valid administrative security role assigned.',
        ]);
    }
}
