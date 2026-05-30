<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

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

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

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
}
