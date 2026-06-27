<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve the tenant slug from the X-Tenant header
        $slug = $request->header('X-Tenant');

        // Return error if the header is missing
        if (! $slug) {
            return response()->json([
                'message' => 'Tenant identifier missing. X-Tenant header is required.',
            ], 400);
        }

        // Retrieve the active tenant from the database
        $tenant = Tenant::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        // Return 404 if the tenant is not found or is inactive
        if (! $tenant) {
            return response()->json([
                'message' => 'Tenant not found or inactive.',
            ], 404);
        }

        // Bind the tenant to the application container for global access
        app()->instance('currentTenant', $tenant);

        // Verify if the authenticated user has access to the requested tenant
        if (
            auth()->check() &&
            auth()->user()->tenant_id &&
            auth()->user()->tenant_id !== $tenant->id
        ) {
            return response()->json([
                'message' => 'Tenant access denied.',
            ], 403);
        }

        // Merge the tenant into the request object for easy controller access
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}
