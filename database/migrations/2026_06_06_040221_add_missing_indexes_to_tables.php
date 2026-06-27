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

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->index('reference_id', 'idx_reference_id');
            $table->index(['type', 'reference_id'], 'idx_type_ref');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('customer_id', 'idx_customer_id');
        });

        Schema::table('gallery_images', function (Blueprint $table) {
            $table->index(['tenant_id', 'is_active'], 'idx_tenant_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_reference_id');
            $table->dropIndex('idx_type_ref');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_customer_id');
        });

        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropIndex('idx_tenant_active');
        });

    }
};
