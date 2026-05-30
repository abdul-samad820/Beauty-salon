<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'email',
        'phone',
        'address',
        'plan',
        'status',
        'settings',
        'trial_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // ── Helpers ────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function getPlanBadgeColorAttribute(): string
    {
        return match ($this->plan) {
            'enterprise' => 'gold',
            'pro' => 'purple',
            default => 'teal',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'badge-active',
            'suspended' => 'badge-suspended',
            default => 'badge-inactive',
        };
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }
}
