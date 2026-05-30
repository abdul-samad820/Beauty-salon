<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Owner web routes ke liye —
 * User ka tenant_id se tenant load karta hai aur
 * app container mein bind karta hai.
 */
class TenantWebMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->tenant_id) {
            abort(403, 'No tenant assigned to this account.');
        }

        $tenant = Tenant::where('id', $user->tenant_id)
            ->where('status', 'active')
            ->first();

        if (! $tenant) {
            Auth::logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Aapka parlour account suspended ya inactive hai.']);
        }

        // Poori app mein accessible
        app()->instance('currentTenant', $tenant);
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}
