<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service → Product mapping table.
 * Hair Spa service uses: Shampoo 20ml + Serum 10ml + Mask 15ml
 *
 * File: database/migrations/2026_06_01_000004_create_service_products_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity_used', 8, 2)->default(1); // Units consumed per appointment
            $table->string('unit')->nullable();                  // "ml", "gm", "pcs"
            $table->timestamps();

            // One service cannot map same product twice
            $table->unique(['service_id', 'product_id']);
            $table->index(['tenant_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_products');
    }
};
