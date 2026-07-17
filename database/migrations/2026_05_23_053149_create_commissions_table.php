<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->decimal('service_price', 8, 2);      // Service  total price
            $table->decimal('commission_percent', 5, 2); // Staff  %
            $table->decimal('commission_amount', 8, 2);  // Actual amount
            $table->unique('appointment_id');
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->index([
                'tenant_id',
                'status',
            ]);

            $table->index([
                'staff_id',
                'status',
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
