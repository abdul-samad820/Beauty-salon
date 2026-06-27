<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIXED: Changed timestamp() to dateTime() for MySQL 5.7 compatibility.
 * TIMESTAMP columns without default values cause errors in strict MySQL 5.7 configurations.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->enum('status', ['active', 'expired', 'cancelled', 'trial'])->default('trial');
            $table->decimal('amount', 10, 2)->default(0);
            $table->dateTime('starts_at');
            $table->dateTime('expires_at');
            $table->dateTime('cancelled_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            // Indexes for optimized querying
            $table->index(['tenant_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
