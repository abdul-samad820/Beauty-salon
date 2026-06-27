<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait BelongsToTenant
{
    /**
     * Boot the trait to hijack model behavior at the system root.
     */
    protected static function bootBelongsToTenant(): void
    {
        // 1. GLOBAL QUERY FILTERING (AUTOMATIC ISOLATION)
        static::addGlobalScope('tenant', function (Builder $builder) {

            // Determine the active tenant from available application instances
            $activeTenant = null;

            if (app()->has('currentTenant')) {
                $activeTenant = app('currentTenant');
            } elseif (app()->has('customerTenant')) {
                $activeTenant = app('customerTenant');
            }

            // Strictly filter the query if a valid active tenant is found
            if ($activeTenant && isset($activeTenant->id)) {
                $builder->where(
                    $builder->getModel()->getTable().'.tenant_id',
                    $activeTenant->id
                );

                return;
            }

            // FAIL-CLOSED SECURITY: Block queries if the tenant context is missing
            // (excluding console commands and test environments)
            if (! app()->runningInConsole() && ! app()->environment('testing')) {

                // SuperAdmin bypass: SuperAdmins have no specific tenant and require cross-tenant access.
                if (app()->has('isSuperAdmin') && app('isSuperAdmin') === true) {
                    return;
                }

                // If no tenant context is found, hard block the query
                throw new UnauthorizedHttpException(
                    'Tenant Context Boundary Missing',
                    'Security Isolation Alert: Tenant context missing; query execution has been blocked.'
                );
            }
        });

        // 2. AUTOMATIC INJECTION & ANTI-TAMPERING GUARANTEE

        static::creating(function (Model $model) {
            $activeTenant = null;
            if (app()->has('currentTenant')) {
                $activeTenant = app('currentTenant');
            } elseif (app()->has('customerTenant')) {
                $activeTenant = app('customerTenant');
            }

            if ($activeTenant && isset($activeTenant->id)) {

                if (! empty($model->tenant_id) && $model->tenant_id !== $activeTenant->id) {
                    throw new \InvalidArgumentException(
                        'Security Violation: tenant_id mismatch detected on model creation.'
                    );
                }
                $model->tenant_id = $activeTenant->id;
            }
        });

        // 3. SECURE PROPERTY IMMUTABILITY HANDLER
        static::updating(function (Model $model) {
            // Prevent unauthorized modification of the tenant_id field at runtime
            if ($model->isDirty('tenant_id')) {
                $model->tenant_id = $model->getOriginal('tenant_id');
                throw new \InvalidArgumentException('Illegal Action: Tenant boundary variables cannot be altered during database runtime.');
            }
        });
    }

    /**
     * Relationship helper to the Tenant model.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
