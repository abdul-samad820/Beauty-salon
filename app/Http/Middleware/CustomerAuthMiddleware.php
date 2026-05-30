<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');

        if (! Auth::check()) {
            return redirect()->route('customer.login', $subdomain);
        }

        $user = Auth::user();
        $tenant = app('customerTenant');

        // Sirf is tenant ka customer — dusre tenant ka customer allow nahi
        if ((int) $user->tenant_id !== (int) $tenant->id) {
            Auth::logout();

            return redirect()->route('customer.login', $subdomain)
                ->withErrors(['email' => 'Ye account is parlour ka nahi hai.']);
        }

        if (! $user->hasRole('customer')) {
            Auth::logout();

            return redirect()->route('customer.login', $subdomain)
                ->withErrors(['email' => 'Sirf customers yahan login kar sakte hain.']);
        }

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('customer.login', $subdomain)
                ->withErrors(['email' => 'Aapka account inactive hai.']);
        }

        return $next($request);
    }
}
