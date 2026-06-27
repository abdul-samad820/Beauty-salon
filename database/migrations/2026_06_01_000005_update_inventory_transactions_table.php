<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add reference_id to inventory_transactions for idempotency.
 * Also update type enum to include 'appointment_deduct'.
 *
 * File: database/migrations/2026_06_01_000005_update_inventory_transactions_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // reference_id — appointment ID jis ke liye deduction hua
            // Idempotency check ke liye use hoga
            if (! Schema::hasColumn('inventory_transactions', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('reason');
            }
        });

        // MySQL me ENUM me naya value add karna — raw statement se
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
        ALTER TABLE inventory_transactions
        MODIFY COLUMN type ENUM(
            'in',
            'out',
            'appointment_deduct'
        ) NOT NULL DEFAULT 'out'
    ");
        }
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('reference_id');
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
        ALTER TABLE inventory_transactions
        MODIFY COLUMN type ENUM(
            'in',
            'out'
        ) NOT NULL DEFAULT 'out'
    ");
        }
    }
};
