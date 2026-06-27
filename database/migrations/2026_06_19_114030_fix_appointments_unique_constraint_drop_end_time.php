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
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('uq_staff_date_time_slot');

            $table->unique(
                ['staff_id', 'appointment_date', 'start_time'],
                'uq_staff_date_time_slot'
            );
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('uq_staff_date_time_slot');

            $table->unique(
                ['staff_id', 'appointment_date', 'start_time', 'end_time'],
                'uq_staff_date_time_slot'
            );
        });
    }
};
