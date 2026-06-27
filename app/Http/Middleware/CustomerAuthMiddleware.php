<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');
        $tenant = app('customerTenant');

        // 1. Check if authenticated via customer guard
        if (! Auth::guard('customer')->check()) {
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json(['message' => 'Unauthorized customer context.'], 401);
            }

            return redirect()->route('customer.login', $subdomain);
        }

        $user = Auth::guard('customer')->user();

        // 2. Cross-Tenant Leakage Protection Boundary
        if ((int) $user->tenant_id !== (int) $tenant->id) {
            Auth::guard('customer')->logout();

            return redirect()->route('customer.login', $subdomain)
                ->withErrors(['email' => 'Alert: This account does not belong to this establishment.']);
        }

        // 3. Status Active Check
        if (isset($user->is_active) && ! $user->is_active) {
            Auth::guard('customer')->logout();

            return redirect()->route('customer.login', $subdomain)
                ->withErrors(['email' => 'Your account is currently inactive.']);
        }

        return $next($request);
    }
}
