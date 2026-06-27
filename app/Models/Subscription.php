<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * File: app/Models/Subscription.php
 */
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'plan_id', 'billing_cycle',
        'status', 'amount', 'starts_at', 'expires_at',
        'cancelled_at', 'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at?->isFuture();

    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function daysLeft(): int
    {
        return $this->expires_at ? max(0, (int) now()->diffInDays($this->expires_at, false)) : 0;

    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'lb-green',
            'trial' => 'lb-gold',
            'expired' => 'lb-red',
            'cancelled' => 'lb-muted',
            default => 'lb-muted',
        };
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
