<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
        'description',
        'settings',
        'trial_ends_at',
        'instagram_url',
        'facebook_url',
        'hero_image',
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

    public function owner()
    {
        return $this->hasOne(User::class)->whereHas(
            'roles', fn ($q) => $q->where('name', 'owner')
        );
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

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    /** All subscriptions for this tenant */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /** Currently active subscription */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest();
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
            'premium' => 'gold',
            'basic' => 'purple',
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

    public function setPlanAttribute($value): void
    {
        if (! app()->environment('testing')) {
            $validPlans = Cache::remember('plan_slugs', 3600, fn () => Plan::pluck('slug')->toArray());

            if (! empty($validPlans) && ! in_array($value, $validPlans)) {
                throw new \InvalidArgumentException("Invalid plan: {$value}");
            }
        }

        $this->attributes['plan'] = $value;
    }

    public function currentPlan(): ?Plan
    {
        return Cache::remember(
            "tenant_plan_{$this->id}", 3600,
            fn () => Plan::where('slug', $this->plan)->first()
        );
    }

    public function canUseFeature(string $feature): bool
    {
        $plan = $this->currentPlan();
        if (! $plan) {
            return false;
        }

        return (bool) $plan->$feature;
    }
}