<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id', 'tenant_id', 'amount',
        'payment_method', 'status', 'transaction_id',
        'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature',
        'notes', 'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'notes' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
