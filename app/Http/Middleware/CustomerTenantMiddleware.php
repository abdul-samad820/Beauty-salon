<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerTenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');

        if (! $subdomain) {
            abort(404);
        }
        // Subdomain se tenant dhundo
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();

        if (! $tenant) {
            abort(404, 'Parlour not found or inactive.');
        }

        // App mein bind karo — controllers mein use hoga
        app()->instance('customerTenant', $tenant);

        return $next($request);
    }
}
