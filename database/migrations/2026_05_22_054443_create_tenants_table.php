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
            $table->string('name');             // Business name — e.g., "Meesu Cosmetics"
            $table->string('slug')->unique();   // Slug for URL structure — e.g., "meesu"
            $table->string('email')->unique();  // Owner contact email
            $table->string('phone');
            $table->text('address')->nullable();
            $table->string('subdomain')->unique(); // Subdomain — e.g., meesu.app.com
            $table->string('plan', 50)->default('free');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('settings')->nullable(); // Stores working hours, timezone, etc.
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
