<?php

namespace App\Providers;

use App\Auth\TenantAwareUserProvider;
use App\Models\Appointment;
use App\Models\Review;
use App\Models\Tenant;
use App\Observers\AppointmentObserver;
use App\Policies\AppointmentPolicy;
use App\Policies\ReviewPolicy;
use App\View\Composers\SidebarComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Auth::provider('tenant_aware_eloquent', function ($app, array $config) {
            return new TenantAwareUserProvider(
                $app['hash'],
                $config['model']
            );
        });
        ResetPassword::createUrlUsing(function (
            object $notifiable,
            string $token
        ) {
            $subdomain = Tenant::where('id', $notifiable->tenant_id)
                ->value('subdomain');

            if ($subdomain) {
                return url(route('customer.password.reset', [
                    'subdomain' => $subdomain,
                    'token' => $token,
                ], false)).'?email='.urlencode($notifiable->getEmailForPasswordReset());
            }

            return url('/reset-password/'.$token.'?email='.urlencode($notifiable->getEmailForPasswordReset()));
        });

        Appointment::observe(AppointmentObserver::class);

        View::composer('partials.superadmin-sidebar', function ($view) {
            $activeTenants = Cache::remember(
                'superadmin_active_tenants_count',
                120, // 2 minutes cache
                fn () => Tenant::where('status', 'active')->count()
            );
            $view->with('activeTenants', $activeTenants);
        });

        View::composer(
            'partials.owner-sidebar',
            SidebarComposer::class
        );
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
