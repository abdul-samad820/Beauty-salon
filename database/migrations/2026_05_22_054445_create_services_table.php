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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->unique([
                'tenant_id',
                'name',
            ]);
            $table->text('description')->nullable();
            $table->enum('category', [
                'hair', 'skin', 'nail', 'bridal', 'massage', 'other',
            ])->default('other');
            $table->integer('duration_minutes');     // 45, 60, 90
            $table->decimal('price', 8, 2);          // 499.00
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
