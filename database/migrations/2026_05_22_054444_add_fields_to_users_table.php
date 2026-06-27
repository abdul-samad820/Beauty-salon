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
        Schema::table('users', function (Blueprint $table) {
            // Nullable because Super Admins do not belong to a specific tenant
            $table->foreignId('tenant_id')
                ->nullable()
                ->constrained('tenants')
                ->nullOnDelete()
                ->after('id');

            $table->string('phone')->nullable();
            $table->unique(['tenant_id', 'phone']);
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'phone']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'phone', 'is_active']);
        });
    }
};
