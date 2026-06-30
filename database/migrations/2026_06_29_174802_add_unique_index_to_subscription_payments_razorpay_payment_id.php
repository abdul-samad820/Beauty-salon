<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PAY-02 Fix — unique constraint on razorpay_payment_id.
 *
 * Note: razorpay_payment_id had NO prior index (only razorpay_order_id
 * was indexed in the earlier migration), so we add unique directly.
 * NULL values are excluded from unique checks in MySQL — safe for
 * cash/manual payments that have no Razorpay ID.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->unique('razorpay_payment_id', 'uq_sub_payments_razorpay_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropUnique('uq_sub_payments_razorpay_payment_id');
        });
    }
};
