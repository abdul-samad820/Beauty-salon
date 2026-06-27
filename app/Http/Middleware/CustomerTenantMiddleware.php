<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CustomerTenantMiddleware
 *
 * PURPOSE:
 * - Identify the tenant by subdomain and bind it to the application.
 * - Attach no-cache headers to prevent the browser from caching redirect loops.
 */
class CustomerTenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');

        if (! $subdomain) {
            abort(404);
        }

        // Retrieve the tenant based on the subdomain
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();

        if (! $tenant) {
            abort(404, 'Parlour not found or inactive.');
        }

        // Bind the tenant to the app container for global access
        app()->instance('customerTenant', $tenant);
        app()->instance('currentTenant', $tenant);

        // ── Apply no-cache headers ──────────────────────────────────
        // This prevents the browser from caching redirects, ensuring
        // that fresh requests are made and resolving potential redirect loops.
        $response = $next($request);

        return $response
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
