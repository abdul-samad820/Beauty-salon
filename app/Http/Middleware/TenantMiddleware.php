<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Request me X-Tenant header dhundo
        $slug = $request->header('X-Tenant');

        // Header nahi bheja? Error do
        if (! $slug) {
            return response()->json([
                'message' => 'Tenant identifier missing. X-Tenant header required.',
            ], 400);
        }

        // Database me tenant dhundo
        $tenant = Tenant::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        // Tenant mila nahi? 404
        if (! $tenant) {
            return response()->json([
                'message' => 'Tenant not found or inactive.',
            ], 404);
        }

        // Tenant mila — app container me store karo
        // Ab poori app me currentTenant access ho sakta hai
        app()->instance('currentTenant', $tenant);
        if (
            auth()->check() &&
            auth()->user()->tenant_id &&
            auth()->user()->tenant_id !== $tenant->id
        ) {
            return response()->json([
                'message' => 'Tenant access denied.',
            ], 403);
        }
        // Request me bhi daal do — controllers me $request->tenant se milega
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}
