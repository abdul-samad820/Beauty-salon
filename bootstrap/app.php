<?php

use App\Http\Middleware\CheckSubscriptionActive;
use App\Http\Middleware\CustomerAuthMiddleware;
use App\Http\Middleware\CustomerTenantMiddleware;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\TenantMiddleware;
use App\Http\Middleware\TenantWebMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->validateCsrfTokens(except: [
            'razorpay/webhook',
        ]);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // ── Customer guard isolation ────────────────────────────────
        //
        // The 'customer' guard uses a dedicated 'customer_session' cookie,
        // while the 'web' guard uses the default 'laravel_session' cookie.
        // This ensures Owner and Customer sessions remain strictly isolated
        // at the browser level.
        $middleware->redirectGuestsTo(function (Request $request) {
            // Redirect to customer login for customer routes
            if ($request->route('subdomain')) {
                $subdomain = $request->route('subdomain');

                return route('customer.login', $subdomain);
            }

            return route('login');
        });
        $middleware->redirectUsersTo(function (Request $request) {
            $user = Auth::user();

            return $user?->dashboardRouteName()
                ? route($user->dashboardRouteName())
                : route('login');
        });

        $middleware->alias([
            // Existing
            'verified' => EnsureEmailIsVerified::class,
            'tenant' => TenantMiddleware::class,

            // Owner web routes
            'tenant.web' => TenantWebMiddleware::class,

            // SuperAdmin routes
            'superadmin' => SuperAdminMiddleware::class,

            // Customer routes — isolated guard
            'customer.tenant' => CustomerTenantMiddleware::class,
            'customer.auth' => CustomerAuthMiddleware::class,

            // Spatie roles and permissions
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'can' => Authorize::class,

            'subscription.active' => CheckSubscriptionActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ── Authentication Exception handling ──────────────
        $exceptions->render(function (
            AuthenticationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Redirect to appropriate login based on context
            if ($request->route('subdomain')) {
                return redirect()->route('customer.login', $request->route('subdomain'));
            }

            return redirect()->route('login');
        });

        // ── Forbidden Access handling ──────────────
        $exceptions->render(function (
            AccessDeniedHttpException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return response()->view('errors.403', [], 403);
            }
        });

        // ── Not Found handling ──────────────
        $exceptions->render(function (
            NotFoundHttpException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return response()->view('errors.404', [], 404);
            }
        });

    })->create();
