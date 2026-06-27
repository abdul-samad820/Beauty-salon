<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use BelongsToTenant, HasFactory , SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'appointment_id',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
