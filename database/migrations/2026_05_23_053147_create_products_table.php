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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');                    // "Loreal Shampoo"
            $table->string('category')->nullable();    // "hair", "skin" etc.
            $table->decimal('price', 8, 2);            // Purchase price
            $table->integer('quantity')->default(0);   // Current stock
            $table->integer('low_stock_threshold')->default(5); // Alert threshold
            $table->boolean('is_active')->default(true);
            $table->index([
                'tenant_id',
                'is_active',
            ]);
            $table->unique([
                'tenant_id',
                'name',
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
        Schema::dropIfExists('products');
    }
};
