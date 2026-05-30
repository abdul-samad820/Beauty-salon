<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    // Model boot hote hi ye automatically chalega
    protected static function bootBelongsToTenant(): void
    {
        // Har query me automatically tenant_id filter lagega
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->has('currentTenant')) {
                $builder->where('tenant_id', app('currentTenant')->id);
            }
        });

        // Naya record create karte waqt tenant_id auto-set hoga
        static::creating(function ($model) {
            if (app()->has('currentTenant') && empty($model->tenant_id)) {
                $model->tenant_id = app('currentTenant')->id;
            }
        });
    }
}
