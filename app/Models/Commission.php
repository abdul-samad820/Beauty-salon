<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
    'tenant_id',
    'staff_id',
    'appointment_id',
    'service_price',
    'commission_percent',
    'commission_amount',
    'status',
];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
