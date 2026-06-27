<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'staff_id',
        'service_id',
        'amount',
        'gst_rate',
        'gst_amount',
        'status',
        'appointment_date',
        'start_time',
        'end_time',
        'notes',
        'reminder_sent',
        'payment_method',
        'payment_status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'reminder_sent' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
