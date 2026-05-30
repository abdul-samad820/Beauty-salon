<?php

use App\Http\Middleware\CustomerAuthMiddleware;
use App\Http\Middleware\CustomerTenantMiddleware;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\TenantMiddleware;
use App\Http\Middleware\TenantWebMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
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

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            // Existing
            'verified' => EnsureEmailIsVerified::class,
            'tenant' => TenantMiddleware::class,

            // Owner web
            'tenant.web' => TenantWebMiddleware::class,

            // SuperAdmin
            'superadmin' => SuperAdminMiddleware::class,

            // Customer — Phase 3
            'customer.tenant' => CustomerTenantMiddleware::class,
            'customer.auth' => CustomerAuthMiddleware::class,

            // Spatie roles
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'can' => Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (
            AuthenticationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        });

        $exceptions->render(function (
            AccessDeniedHttpException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return response()->view('errors.403', [], 403);
            }
        });

        $exceptions->render(function (
            NotFoundHttpException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return response()->view('errors.404', [], 404);
            }
        });

    })->create();
