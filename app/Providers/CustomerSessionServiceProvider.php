<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class CustomerSessionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $request = $this->app['request'];

        // Project uses path-based routing: /{subdomain}/...
        // Extract subdomain from URL path segment instead of host
        $segments = explode('/', trim($request->getPathInfo(), '/'));
        $firstSegment = $segments[0] ?? '';

        $reservedPaths = [
            'login',
            'logout',
            'owner',
            'superadmin',
            'email',
            'staff',
        ];

        if ($firstSegment && ! in_array($firstSegment, $reservedPaths)) {
            Config::set('session.cookie', 'customer_session_token');
            Config::set('session.path', '/');
        }
    }
}
