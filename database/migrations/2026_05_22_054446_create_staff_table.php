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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('commission_percent', 5, 2)->default(0); // e.g., 30.00 = 30%
            $table->json('specializations')->nullable(); // e.g., ["facial", "waxing", "nails"]
            $table->json('working_hours')->nullable();
            // Format example: {"mon":"09:00-20:00","tue":"09:00-20:00","sun":null}
            // A null value indicates the staff member is unavailable on that day.
            $table->boolean('is_available')->default(true); // False if on leave
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
