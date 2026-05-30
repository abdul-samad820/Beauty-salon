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
            $table->decimal('commission_percent', 5, 2)->default(0); // 30.00 = 30%
            $table->json('specializations')->nullable(); // ["facial","waxing","nail"]
            $table->json('working_hours')->nullable();
            // Format: {"mon":"09:00-20:00","tue":"09:00-20:00","sun":null}
            // null matlab us din band hai
            $table->boolean('is_available')->default(true); // Leave pe hai toh false
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
