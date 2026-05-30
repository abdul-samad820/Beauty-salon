<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
        if ($request->expectsJson()) {

            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);

        }

        if (! Auth::user()->hasRole('super_admin')) {
            // Owner ko uske dashboard pe bhejo
            if (Auth::user()->hasRole('owner')) {
                return redirect()->route('owner.dashboard');
            }

            abort(403, 'Access denied. Super Admin only.');
        }

        return $next($request);
    }
}
