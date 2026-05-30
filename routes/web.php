<?php

use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
// Auth
use App\Http\Controllers\Web\AuthWebController;
// Owner Web Controllers
use App\Http\Controllers\Web\Customer\AppointmentController as CustomerAppointmentController;
use App\Http\Controllers\Web\Customer\CustomerAuthController;
use App\Http\Controllers\Web\Customer\HomeController as CustomerHomeController;
use App\Http\Controllers\Web\Owner\AnalyticsWebController;
use App\Http\Controllers\Web\Owner\AppointmentWebController;
use App\Http\Controllers\Web\Owner\CommissionWebController;
use App\Http\Controllers\Web\Owner\DashboardController;
use App\Http\Controllers\Web\Owner\InventoryWebController;
// SuperAdmin Controllers
use App\Http\Controllers\Web\Owner\ServiceWebController;
// Customer Controllers
use App\Http\Controllers\Web\Owner\SettingsWebController;
use App\Http\Controllers\Web\Owner\StaffWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| OWNER / SUPERADMIN AUTH
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthWebController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthWebController::class, 'login'])
        ->name('login.post');

});

Route::post('/logout', [AuthWebController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| SUPERADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {

        Route::get('/dashboard', [SuperAdminTenantController::class, 'dashboard'])->name('dashboard');
        Route::resource('tenants', SuperAdminTenantController::class);
        Route::patch('/tenants/{tenant}/status', [SuperAdminTenantController::class, 'updateStatus'])->name('tenants.status');
    });

/*
|--------------------------------------------------------------------------
| OWNER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant.web'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class,   'index'])->name('dashboard');
        Route::get('/analytics', [AnalyticsWebController::class, 'index'])->name('analytics');

        // Appointments
        Route::get('/appointments', [AppointmentWebController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/today', [AppointmentWebController::class, 'today'])->name('appointments.today');
        Route::post('/appointments', [AppointmentWebController::class, 'store'])->name('appointments.store');
        Route::post('/appointments/{id}/status', [AppointmentWebController::class, 'updateStatus'])->name('appointments.status');

        // Services
        Route::get('/services', [ServiceWebController::class, 'index'])->name('services.index');
        Route::post('/services', [ServiceWebController::class, 'store'])->name('services.store');
        Route::put('/services/{id}', [ServiceWebController::class, 'update'])->name('services.update');
        Route::delete('/services/{id}', [ServiceWebController::class, 'destroy'])->name('services.destroy');

        // Staff
        Route::get('/staff', [StaffWebController::class, 'index'])->name('staff.index');
        Route::post('/staff', [StaffWebController::class, 'store'])->name('staff.store');
        Route::put('/staff/{id}', [StaffWebController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{id}', [StaffWebController::class, 'destroy'])->name('staff.destroy');

        // Inventory
        Route::get('/inventory', [InventoryWebController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryWebController::class, 'store'])->name('inventory.store');
        Route::post('/inventory/stock-in', [InventoryWebController::class, 'stockIn'])->name('inventory.stock-in');
        Route::post('/inventory/stock-out', [InventoryWebController::class, 'stockOut'])->name('inventory.stock-out');

        // Commissions
        Route::get('/commissions', [CommissionWebController::class, 'index'])->name('commissions.index');
        Route::post('/commissions/{staffId}/mark-paid', [CommissionWebController::class, 'markAsPaid'])->name('commissions.mark-paid');

        // Settings
        Route::get('/settings', [SettingsWebController::class, 'index'])->name('settings');
        Route::put('/settings', [SettingsWebController::class, 'update'])->name('settings.update');
    });

/*
|--------------------------------------------------------------------------
| CUSTOMER ROUTES  —  /{subdomain}/...
|--------------------------------------------------------------------------
*/
Route::prefix('{subdomain}')
    ->middleware('customer.tenant')   // CustomerTenantMiddleware — tenant bind karo
    ->group(function () {

        // ── Guest only (login/register) ──
        Route::middleware('guest')->group(function () {
            Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
            Route::post('/login', [CustomerAuthController::class, 'login'])->name('customer.login.post');
            Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
            Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register.post');
        });

        // ── Logout ──
        Route::post('/logout', [CustomerAuthController::class, 'logout'])
            ->middleware('customer.auth')
            ->name('customer.logout');

        // ── Protected customer routes ──
        Route::middleware('customer.auth')->group(function () {
            Route::get('/', [CustomerHomeController::class,       'index'])->name('customer.home');
            Route::get('/slots', [CustomerHomeController::class,       'slots'])->name('customer.slots');
            Route::post('/book', [CustomerHomeController::class,       'book'])->name('customer.book');
            Route::get('/appointments', [CustomerAppointmentController::class, 'index'])->name('customer.appointments');
            Route::post('/appointments/{id}/cancel', [CustomerAppointmentController::class, 'cancel'])->name('customer.appointments.cancel');
        });
    });
