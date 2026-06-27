<?php

use App\Http\Controllers\Customer\SlotController;
use App\Http\Controllers\SuperAdmin\AnalyticsController as SuperAdminAnalyticsController;
use App\Http\Controllers\SuperAdmin\AppointmentMonitorController;
use App\Http\Controllers\SuperAdmin\QueueMonitorController;
use App\Http\Controllers\SuperAdmin\RevenueController as SuperAdminRevenueController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use App\Http\Controllers\SuperAdmin\SubscriptionController as SuperAdminSubscriptionController;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\Customer\AppointmentController as CustomerAppointmentController;
use App\Http\Controllers\Web\Customer\CustomerAuthController;
use App\Http\Controllers\Web\Customer\CustomerPaymentController;
use App\Http\Controllers\Web\Customer\HomeController as CustomerHomeController;
use App\Http\Controllers\Web\Customer\InvoiceController;
use App\Http\Controllers\Web\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Web\Customer\ReviewController;
use App\Http\Controllers\Web\Owner\AnalyticsWebController;
use App\Http\Controllers\Web\Owner\AppointmentWebController;
use App\Http\Controllers\Web\Owner\CommissionTierController;
use App\Http\Controllers\Web\Owner\CommissionWebController;
use App\Http\Controllers\Web\Owner\CustomerWebController;
use App\Http\Controllers\Web\Owner\DashboardController;
use App\Http\Controllers\Web\Owner\GalleryWebController;
use App\Http\Controllers\Web\Owner\InventoryValuationController;
use App\Http\Controllers\Web\Owner\InventoryWebController;
use App\Http\Controllers\Web\Owner\NotificationController;
use App\Http\Controllers\Web\Owner\ProfileController;
use App\Http\Controllers\Web\Owner\RazorpayController;
use App\Http\Controllers\Web\Owner\RazorpayWebhookController;
use App\Http\Controllers\Web\Owner\ReviewWebController;
use App\Http\Controllers\Web\Owner\ServiceProductController;
use App\Http\Controllers\Web\Owner\ServiceWebController;
use App\Http\Controllers\Web\Owner\SettingsWebController;
use App\Http\Controllers\Web\Owner\StaffWebController;
use App\Http\Controllers\Web\Staff\StaffDashboardController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Razorpay Webhook — CSRF exempt
Route::post('/razorpay/webhook',
    [RazorpayWebhookController::class, 'handle']
)->name('razorpay.webhook');
/*
|--------------------------------------------------------------------------
| Web Routes Identity Layer Matrix
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        $route = $user->dashboardRouteName();

        if ($route) {
            return redirect()->route($route);
        }

        Auth::logout();
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
});

Route::post('/logout', [AuthWebController::class, 'logout'])->middleware('auth')->name('logout');

// Health Check Endpoint
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $dbStatus = 'ok';
    } catch (Exception $e) {
        $dbStatus = 'error';
    }

    $status = $dbStatus === 'ok' ? 200 : 503;

    return response()->json([
        'status' => $dbStatus === 'ok' ? 'healthy' : 'unhealthy',
        'timestamp' => now()->toISOString(),
        'services' => [
            'database' => $dbStatus,
            'cache' => 'ok',
        ],
    ], $status);
})->name('health');

Route::middleware(['auth', 'verified', 'tenant.web', 'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        Route::get('/subscription-expired', function () {
            return view('owner.subscription.expired');
        })->name('subscription.expired');

        // Billing & Razorpay — outside subscription.active so expired owners can upgrade
        Route::get('/billing', [RazorpayController::class, 'billing'])->name('billing');
        Route::post('/razorpay/create-order', [RazorpayController::class, 'createOrder'])->name('razorpay.create-order');
        Route::post('/razorpay/verify', [RazorpayController::class, 'verifyPayment'])->name('razorpay.verify');
    });

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('owner.dashboard')->with('success', 'Email verified successfully!');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});

// CSRF is handled by global web middleware group — do not remove web middleware
Route::middleware(['auth', 'tenant.web', 'role:staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/appointments', [StaffDashboardController::class, 'appointments'])->name('appointments');
        Route::get('/commissions', [StaffDashboardController::class, 'commissions'])->name('commissions');
        Route::get('/profile', [StaffDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [StaffDashboardController::class, 'updateProfile'])->name('profile.update');
    });
/*
|--------------------------------------------------------------------------
| Super Admin Protected Core Console (SEC-015 Fixed Alias Mappings)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {

        // Dashboard View Catalyst Endpoint
        Route::get('/dashboard', [SuperAdminTenantController::class, 'dashboard'])->name('dashboard');
        Route::get('/live-stats', [SuperAdminTenantController::class, 'liveStats'])->name('stats.live');
        // Tenants Portfolio Operations
        Route::resource('tenants', SuperAdminTenantController::class);
        Route::patch('/tenants/{tenant}/status', [SuperAdminTenantController::class, 'updateStatus'])->name('tenants.status');
        Route::get('/notifications', [SuperAdminTenantController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/mark-read', [SuperAdminTenantController::class, 'markNotificationsRead'])->name('notifications.mark-read');

        // Platform Telemetry Graphs
        Route::get('/analytics', [SuperAdminAnalyticsController::class, 'index'])->name('analytics');
        Route::get('/revenue', [SuperAdminRevenueController::class, 'index'])->name('revenue');
        Route::get('/appointments', [AppointmentMonitorController::class, 'index'])->name('appointments');

        // Plans & Multi-Tenant Subscription Contracts
        Route::get('/subscriptions', [SuperAdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions', [SuperAdminSubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::post('/subscriptions/{subscription}/cancel', [SuperAdminSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('/subscriptions/{subscription}/renew', [SuperAdminSubscriptionController::class, 'renew'])->name('subscriptions.renew');

        Route::get('/plans', [SuperAdminSubscriptionController::class, 'plans'])->name('plans.index');
        Route::post('/plans', [SuperAdminSubscriptionController::class, 'storePlan'])->name('plans.store');
        Route::put('/plans/{plan}', [SuperAdminSubscriptionController::class, 'updatePlan'])->name('plans.update');

        // Asynchronous System Job Handlers
        Route::get('/queue', [QueueMonitorController::class, 'index'])->name('queue.index');
        Route::post('/queue/retry/{uuid}', [QueueMonitorController::class, 'retry'])->name('queue.retry');
        Route::delete('/queue/failed/{uuid}', [QueueMonitorController::class, 'deleteFailedJob'])->name('queue.failed.delete');
        Route::post('/queue/flush', [QueueMonitorController::class, 'flushFailed'])->name('queue.flush');

        // Global System Variables Options
        Route::get('/settings', [SuperAdminSettingsController::class, 'index'])->name('settings');
        Route::put('/settings', [SuperAdminSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/clear-cache', [SuperAdminSettingsController::class, 'clearCache'])->name('settings.clear-cache');
    });

/*
|--------------------------------------------------------------------------
| Salon Owner Operational Workspace Suite
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'tenant.web', 'role:owner', 'subscription.active'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/notifications/new-bookings', [DashboardController::class, 'newBookingsCount'])->name('notifications.bookings');
        Route::get('/analytics', [AnalyticsWebController::class, 'index'])->name('analytics');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.list');
        Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');

        // Schedule Registers
        Route::get('/appointments', [AppointmentWebController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/create', [AppointmentWebController::class, 'create'])->name('appointments.create');
        Route::get('/appointments/today', [AppointmentWebController::class, 'today'])->name('appointments.today');
        Route::get('/appointments/export', [AppointmentWebController::class, 'export'])->name('appointments.export');

        Route::post('/appointments', [AppointmentWebController::class, 'store'])->name('appointments.store');
        Route::post('/appointments/{id}/status', [AppointmentWebController::class, 'updateStatus'])->name('appointments.status');

        // Treatments Ledger
        Route::get('/services', [ServiceWebController::class, 'index'])->name('services.index');
        Route::post('/services', [ServiceWebController::class, 'store'])->name('services.store');
        Route::put('/services/{id}', [ServiceWebController::class, 'update'])->name('services.update');
        Route::delete('/services/{id}', [ServiceWebController::class, 'destroy'])->name('services.destroy');

        // Staff Personnel Allocation
        Route::get('/staff', [StaffWebController::class, 'index'])->name('staff.index');
        Route::post('/staff', [StaffWebController::class, 'store'])->name('staff.store');
        Route::put('/staff/{id}', [StaffWebController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{id}', [StaffWebController::class, 'destroy'])->name('staff.destroy');

        // Customer History
        Route::get('/customers', [CustomerWebController::class, 'index'])->name('customers.index');
        Route::get('/customers/{id}', [CustomerWebController::class, 'show'])->name('customers.show');

        // Commission Tiers (per staff)
        Route::post('/staff/{staffId}/tiers', [CommissionTierController::class, 'store'])->name('staff.tiers.store');
        Route::delete('/staff/tiers/{tierId}', [CommissionTierController::class, 'destroy'])->name('staff.tiers.destroy');

        // Gallery Management
        Route::get('/gallery', [GalleryWebController::class, 'index'])->name('gallery.index');
        Route::post('/gallery', [GalleryWebController::class, 'store'])->name('gallery.store');
        Route::post('/gallery/reorder', [GalleryWebController::class, 'reorder'])->name('gallery.reorder');
        Route::delete('/gallery/{id}', [GalleryWebController::class, 'destroy'])->name('gallery.destroy');

        // Inventory Stock Control Assets
        Route::get('/inventory', [InventoryWebController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/valuation', [InventoryValuationController::class, 'index'])->name('inventory.valuation');
        Route::post('/inventory', [InventoryWebController::class, 'store'])->name('inventory.store');
        Route::post('/inventory/stock-in', [InventoryWebController::class, 'stockIn'])->name('inventory.stock-in');
        Route::post('/inventory/stock-out', [InventoryWebController::class, 'stockOut'])->name('inventory.stock-out');
        Route::put('/products/{id}', [InventoryWebController::class, 'update'])->name('inventory.update');

        Route::get('/inventory/service-mapping', [ServiceProductController::class, 'index'])->name('inventory.service-mapping');
        Route::post('/inventory/service-mapping', [ServiceProductController::class, 'store'])->name('inventory.service-mapping.store');
        Route::delete('/inventory/service-mapping/{id}', [ServiceProductController::class, 'destroy'])->name('inventory.service-mapping.destroy');
        Route::get('/inventory/service-mapping/for-service', [ServiceProductController::class, 'forService'])->name('inventory.service-mapping.for-service');

        // Fiscal Ledger Statements
        Route::get('/commissions', [CommissionWebController::class, 'index'])->name('commissions.index');
        Route::post('/commissions/{staffId}/mark-paid', [CommissionWebController::class, 'markAsPaid'])->name('commissions.mark-paid');
        Route::post('/commissions/{id}/settle', [CommissionWebController::class, 'settle'])->name('commissions.settle');

        // Profile Identity Anchors
        Route::get('/settings', [SettingsWebController::class, 'index'])->name('settings');
        Route::put('/settings', [SettingsWebController::class, 'update'])->name('settings.update');
        Route::put('/settings/password', [SettingsWebController::class, 'updatePassword'])->name('settings.password');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');          // FIX: group prefix 'owner.' already applies
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');   // FIX: was owner.owner.profile.update
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        // Reviews Management
        Route::get('/reviews', [ReviewWebController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{id}/approve', [ReviewWebController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{id}/reject', [ReviewWebController::class, 'reject'])->name('reviews.reject');
    });

/*
|--------------------------------------------------------------------------
| Isolated Consumer Multi-Tenant Subdomain Loop Framework
|--------------------------------------------------------------------------
*/
Route::prefix('{subdomain}')
    ->where(['subdomain' => '[a-z0-9\-]+'])
    ->middleware('customer.tenant')
    ->group(function () {

        // NAYA
        Route::middleware('guest')->group(function () {
            Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
            Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('customer.login.post');
            Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
            Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:3,1')->name('customer.register.post');
            Route::get('/forgot-password', [CustomerAuthController::class, 'showForgotPassword'])->name('customer.password.request');
            Route::post('/forgot-password', [CustomerAuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('customer.password.email');
            Route::get('/reset-password/{token}', [CustomerAuthController::class, 'showResetForm'])->name('customer.password.reset');
            Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword'])->name('customer.password.update');
        });

        // Public landing page — no auth required
        Route::get('/landing', [CustomerHomeController::class, 'landing'])->name('customer.landing');

        Route::post('/logout', [CustomerAuthController::class, 'logout'])->middleware('customer.auth')->name('customer.logout');

        Route::middleware('customer.auth')->group(function () {
            Route::get('/', [CustomerHomeController::class, 'index'])->name('customer.home');
            Route::get('/slots', [SlotController::class, 'index'])->name('customer.slots');
            Route::post('/book', [CustomerHomeController::class, 'book'])->name('customer.book');

            // FIXED A5: Aligned and secured explicit get named route tracking node for confirmation invoices
            Route::get('/booking-confirmed/{id}', [CustomerHomeController::class, 'bookingConfirmed'])->name('customer.book.confirmed');

            Route::post('/appointments/{appointmentId}/payment/create-order', [CustomerPaymentController::class, 'createOrder'])
                ->name('customer.payment.create-order');

            Route::post('/appointments/{appointmentId}/payment/verify', [CustomerPaymentController::class, 'verifyPayment'])
                 ->name('customer.payment.verify');
            Route::get('/appointments', [CustomerAppointmentController::class, 'index'])->name('customer.appointments');
            Route::post('/appointments/{id}/cancel', [CustomerAppointmentController::class, 'cancel'])->name('customer.appointments.cancel');

            Route::get('/profile', [CustomerProfileController::class, 'index'])->name('customer.profile');
            Route::put('/profile', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
            Route::put('/profile/password', [CustomerProfileController::class, 'updatePassword'])->name('customer.profile.password');

            Route::get('/services', [CustomerHomeController::class, 'services'])->name('customer.services');
            Route::get('/review/{appointmentId}', [ReviewController::class, 'create'])->name('customer.review.create');
            Route::post('/review/{appointmentId}', [ReviewController::class, 'store'])->name('customer.review.store');
            Route::get('/products', [CustomerHomeController::class, 'products'])->name('customer.products');
            Route::get('/gallery', [CustomerHomeController::class, 'gallery'])->name('customer.gallery');

            Route::get('/appointments/{id}/invoice', [InvoiceController::class, 'download'])
                ->name('customer.invoice.download');
        });
    });
