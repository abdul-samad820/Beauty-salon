<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // ── Relationships ──────────────────────────────

    // ── Helpers ────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        // FIXED: Underscore hata diya taaki nayi settings aur db se match kare
        return $this->hasRole('superadmin');
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    /**
     * Single source of truth for role -> dashboard route resolution.
     * Used by AuthWebController, bootstrap/app.php (redirectUsersTo),
     * and the '/' route in routes/web.php — update ONLY here when adding
     * a new role, so all three stay in sync automatically.
     *
     * Returns null if the user has none of the recognized roles.
     */
    public function dashboardRouteName(): ?string
    {
        return match (true) {
            $this->isSuperAdmin() => 'superadmin.dashboard',
            $this->isOwner() => 'owner.dashboard',
            $this->isStaff() => 'staff.dashboard',
            default => null,
        };
    }

    public function getInitialsAttribute(): string
    {
        if (empty($this->name)) {
            return '??';
        }

        $words = explode(' ', trim($this->name));
        if (count($words) >= 2) {
            return strtoupper($words[0][0].$words[1][0]);
        }

        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Customer-only relation.
     * This relation is only meaningful when this User record
     * belongs to a customer (i.e. resolved via the Customer guard/
     * subdomain context). For owner/staff/superadmin users this
     * will always return an empty collection.
     */
    public function appointments()
    {
        return $this->hasMany(
            Appointment::class,
            'customer_id'
        );
    }
}
