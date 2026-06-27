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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('appointment_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', [
                'pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show',
            ])->default('pending')->index();
            $table->text('notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->index(['tenant_id', 'appointment_date']);
            $table->index(['staff_id', 'appointment_date']);
            $table->unique([
                'staff_id',
                'appointment_date',
                'start_time',
            ]);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
