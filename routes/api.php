<?php

use App\Http\Controllers\Auth\AuthController;
// Auth Controllers
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\TenantRegisterController;
use App\Http\Controllers\Customer\AppointmentController as CustomerAppointmentController;
// Owner Controllers
use App\Http\Controllers\Customer\SlotController;
use App\Http\Controllers\Owner\AnalyticsController;
use App\Http\Controllers\Owner\AppointmentController as OwnerAppointmentController;
use App\Http\Controllers\Owner\CommissionController;
use App\Http\Controllers\Owner\InventoryController;
use App\Http\Controllers\Owner\ProductController;
use App\Http\Controllers\Owner\ServiceController;
// Customer Controllers
use App\Http\Controllers\Owner\StaffController;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/register', [TenantRegisterController::class, 'register'])
            ->middleware('throttle:3,1');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1');

        Route::middleware('tenant')->post(
            '/customer/register',
            [CustomerAuthController::class, 'register']
        )->middleware('throttle:3,1');
    });

    /*
    |--------------------------------------------------------------------------
    | OWNER + CUSTOMER ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware(['tenant', 'auth:sanctum', 'throttle:60,1'])->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);

        /*
        |--------------------------------------------------------------------------
        | OWNER
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:owner'])
            ->prefix('owner')
            ->group(function () {

                Route::apiResource('services', ServiceController::class);
                Route::apiResource('staff', StaffController::class);

                Route::get(
                    '/appointments/today',
                    [OwnerAppointmentController::class, 'today']
                );

                Route::get(
                    '/appointments',
                    [OwnerAppointmentController::class, 'index']
                );

                Route::patch(
                    '/appointments/{id}/status',
                    [OwnerAppointmentController::class, 'updateStatus']
                );

                Route::apiResource('products', ProductController::class);

                Route::get(
                    '/products-low-stock',
                    [ProductController::class, 'lowStock']
                );

                Route::post(
                    '/inventory/stock-in',
                    [InventoryController::class, 'stockIn']
                );

                Route::post(
                    '/inventory/stock-out',
                    [InventoryController::class, 'stockOut']
                );

                Route::get(
                    '/commissions',
                    [CommissionController::class, 'index']
                );

                Route::get(
                    '/commissions/staff-summary',
                    [CommissionController::class, 'staffSummary']
                );

                Route::patch(
                    '/commissions/{staffId}/mark-paid',
                    [CommissionController::class, 'markAsPaid']
                );

                Route::get(
                    '/analytics/summary',
                    [AnalyticsController::class, 'summary']
                );

                Route::get(
                    '/analytics/revenue',
                    [AnalyticsController::class, 'revenue']
                );

                Route::get(
                    '/analytics/services',
                    [AnalyticsController::class, 'services']
                );

                Route::get(
                    '/analytics/customers',
                    [AnalyticsController::class, 'customers']
                );
            });

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:customer'])
            ->prefix('customer')
            ->group(function () {

                Route::get('/slots', [SlotController::class, 'index']);

                Route::get(
                    '/appointments',
                    [CustomerAppointmentController::class, 'index']
                );

                Route::post(
                    '/appointments',
                    [CustomerAppointmentController::class, 'store']
                );

                Route::patch(
                    '/appointments/{id}/cancel',
                    [CustomerAppointmentController::class, 'cancel']
                );
            });
    });

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN
    |s--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:superadmin'])
        ->prefix('admin')
        ->group(function () {

            Route::get('/tenants', function () {

                return response()->json([
                    'message' => 'All tenants',
                    'data' => Tenant::paginate(50),
                ]);
            });
        });
});
