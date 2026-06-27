<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Redirect to login if the user is not authenticated
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Grant access if the user has the 'superadmin' role
        if ($user->hasRole('superadmin')) {
            // Register a superadmin context flag in the application container.
            // The BelongsToTenant trait uses this flag to bypass tenant filtering,
            // allowing the SuperAdmin to view platform-wide data across all tenants.
            app()->instance('isSuperAdmin', true);

            return $next($request);
        }

        // 3. Redirect Owners to their respective dashboards
        if ($user->hasRole('owner')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden access scope.'], 403);
            }

            return redirect()->route('owner.dashboard');
        }

        // 4. Fallback: If roles do not match, clear the session and redirect to login
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden: Administrative permissions required.'], 403);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => 'Access Denied: Super Admin permissions could not be verified. Please log in again.',
        ]);
    }
}
