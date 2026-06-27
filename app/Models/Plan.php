<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * File: app/Models/Plan.php
 */
class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description',
        'price_monthly', 'price_yearly',
        'max_staff', 'max_services', 'max_appointments_per_month',
        'inventory_enabled', 'analytics_enabled', 'commission_enabled',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'inventory_enabled' => 'boolean',
        'analytics_enabled' => 'boolean',
        'commission_enabled' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function getBadgeColorAttribute(): string
    {
        return match ($this->slug) {
            'premium' => 'gold',
            'basic' => 'purple',
            default => 'teal',
        };
    }

    public function getYearlySavingAttribute(): float
    {
        $yearlyIfMonthly = $this->price_monthly * 12;

        return max(0, $yearlyIfMonthly - $this->price_yearly);
    }
}
