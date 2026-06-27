<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscriptionActive
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = app('currentTenant');

        if (! $tenant) {
            return $next($request);
        }

        // Trial expired check
        if ($tenant->trial_ends_at && now()->gt($tenant->trial_ends_at)) {
            if ($tenant->status !== 'active' || ! $tenant->subscriptions()->where('status', 'active')->exists()) {
                return redirect()->route('owner.subscription.expired')
                    ->with('error', 'Your trial has expired. Please upgrade to continue.');
            }
        }

        // Suspended tenant check
        if ($tenant->status === 'suspended') {
            return redirect()->route('owner.subscription.expired')
                ->with('error', 'Your account has been suspended. Please contact support.');
        }

        return $next($request);
    }
}
