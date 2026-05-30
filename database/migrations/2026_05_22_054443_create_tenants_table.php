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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Parlour ka naam — "Meesu Cosmetics"
            $table->string('slug')->unique();    // URL me use hoga — "meesu"
            $table->string('email')->unique();   // Owner ka email
            $table->string('phone');
            $table->text('address')->nullable();
            $table->string('subdomain')->unique(); // "meesu" → meesu.app.com
            $table->enum('plan', ['free', 'pro', 'enterprise'])->default('free');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('settings')->nullable(); // Working hours, timezone etc.
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
