<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use BelongsToTenant, HasFactory , SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'appointment_id',
        'service_price',
        'commission_percent',
        'commission_amount',
        'status',
    ];

    protected $casts = [
        'service_price' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',

    ];

    const STATUS_PENDING = 'pending';

    const STATUS_PAID = 'paid';

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
