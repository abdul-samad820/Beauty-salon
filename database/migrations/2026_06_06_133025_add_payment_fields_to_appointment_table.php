<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1 — payment_method ENUM me razorpay add karo
        // SQLite MODIFY COLUMN support nahi karta, isliye driver check karo
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN payment_method ENUM('cash', 'upi', 'razorpay') NOT NULL DEFAULT 'cash'");
        }
        // SQLite mein ENUM TEXT ke roop mein store hota hai,
        // column already exist karta hai — kuch karna nahi padta

        // Step 2 — Razorpay fields + payment_status add karo
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'not_required'])
                ->default('not_required')
                ->after('payment_method');

            $table->string('razorpay_order_id')->nullable()->after('payment_status');
            $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            $table->string('razorpay_signature')->nullable()->after('razorpay_payment_id');

            $table->index('razorpay_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['razorpay_order_id']);
            $table->dropColumn(['payment_status', 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("UPDATE appointments SET payment_method = 'cash' WHERE payment_method = 'razorpay'");
            DB::statement("ALTER TABLE appointments MODIFY COLUMN payment_method ENUM('cash', 'upi') NOT NULL DEFAULT 'cash'");
        }
    }
};
