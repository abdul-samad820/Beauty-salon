<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'commission_percent',
        'specializations',
        'working_hours',
        'is_available',
    ];

    protected $casts = [
        'specializations' => 'array',
        'working_hours' => 'array',
        'is_available' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function commissionTiers()
    {
        return $this->hasMany(CommissionTier::class)->orderBy('min_revenue');
    }
}
