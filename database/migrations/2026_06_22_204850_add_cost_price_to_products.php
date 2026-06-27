<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds cost_price (purchase/wholesale cost) to products, separate from
 * the existing `price` column which is the customer-facing selling price
 * (see resources/views/customer/products/index.blade.php where `price`
 * renders directly as the retail price shown to customers).
 *
 * cost_price is nullable: existing products won't have historical cost
 * data, and valuation reports should treat a missing cost_price as
 * "unknown cost" rather than silently assuming zero.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 8, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
