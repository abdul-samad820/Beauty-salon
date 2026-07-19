<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * TenantWebMiddleware
 * * Handles loading the tenant based on the authenticated user's tenant_id
 * and binds it to the application container for web routes.
 */
class TenantWebMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Ensure the user has the 'owner' role
        if (! $user->hasAnyRole(['owner', 'staff'])) {
            abort(403, 'Access restricted to salon staff and owners.');
        }

        // Verify the user is associated with a tenant
        if (! $user->tenant_id) {
            abort(403, 'No tenant assigned to this account.');
        }

        // Retrieve the tenant and verify active status
        // Redis mein 30 second ke liye cache karte hain — har request pe
        // Paris (Clever Cloud) tak DB round-trip se bachne ke liye
        $tenant = \Illuminate\Support\Facades\Cache::remember(
            'tenant:'.$user->tenant_id,
            30,
            fn () => Tenant::where('id', $user->tenant_id)
                ->where('status', 'active')
                ->first()
        );

        if (! $tenant) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your parlour account is suspended or inactive.']);
        }

        // Bind the tenant to the application container for global access
        app()->instance('currentTenant', $tenant);
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}