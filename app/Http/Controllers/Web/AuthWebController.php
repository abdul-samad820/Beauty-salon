<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthWebController extends Controller
{
    /**
     * Login form dikhao
     */
    public function showLogin()
    {
        // Pehle se logged in hai to redirect karo
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Login process karo — role ke hisaab se redirect
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
                ->withErrors(['email' => 'Email ya password galat hai.']);
        }

        $user = Auth::user();

        // Inactive user block karo
        if (! $user->is_active) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Aapka account inactive hai. Admin se contact karo.']);
        }

        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Aap logout ho gaye hain.');
    }

    /**
     * Role ke hisaab se redirect karo
     */
    private function redirectByRole($user)
    {
        if ($user->hasRole('super_admin')) {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->hasRole('owner')) {
            return redirect()->route('owner.dashboard');
        }

        // Unknown role — logout karke wapas bhejo
        Auth::logout();

        return redirect()->route('login')->withErrors(['email' => 'Aapke account ka role set nahi hai.']);
    }
}
