<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory,SoftDeletes;

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

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
