<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * File: app/Models/Service.php
 * UPDATED: Added serviceProducts() relationship for inventory automation.
 */
class Service extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'description',
        'duration_minutes',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Products consumed when this service is performed.
     * Used by AppointmentObserver for auto inventory deduction.
     */
    public function serviceProducts()
    {
        return $this->hasMany(ServiceProduct::class);
    }

    /**
     * Actual Product models via pivot (convenience accessor).
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'service_products')
            ->withPivot('quantity_used', 'unit')
            ->withTimestamps();
    }
}
