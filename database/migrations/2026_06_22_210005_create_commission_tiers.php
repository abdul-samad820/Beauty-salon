<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Commission tiers per staff member.
 *
 * Each row defines a revenue slab for a staff member's monthly earnings:
 *   min_revenue <= monthly_revenue < max_revenue -> commission_percent applies.
 *
 * max_revenue = NULL means "no upper limit" (the top tier).
 *
 * Example for one staff member:
 *   (0,    50000, 10)   => ₹0–50k earned this month  → 10%
 *   (50000, 100000, 15) => ₹50k–1L earned this month → 15%
 *   (100000, NULL, 20)  => ₹1L+ earned this month    → 20%
 *
 * If no tiers are defined for a staff member, the observer falls back to
 * the flat Staff.commission_percent value (backward-compatible).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->decimal('min_revenue', 10, 2)->default(0);
            $table->decimal('max_revenue', 10, 2)->nullable();
            $table->decimal('commission_percent', 5, 2);
            $table->timestamps();

            // Prevent overlapping tier definitions per staff member.
            $table->index(['staff_id', 'min_revenue']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_tiers');
    }
};
