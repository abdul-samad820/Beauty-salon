<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * File: database/migrations/2026_06_01_000001_create_plans_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                            // "Free", "Basic", "Premium"
            $table->string('slug')->unique();                  // "free", "basic", "premium"
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->integer('max_staff')->default(2);
            $table->integer('max_services')->default(10);
            $table->integer('max_appointments_per_month')->default(100);
            $table->boolean('inventory_enabled')->default(false);
            $table->boolean('analytics_enabled')->default(false);
            $table->boolean('commission_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
