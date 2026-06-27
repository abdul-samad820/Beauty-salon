<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {

            $table->index('customer_id', 'idx_appointments_customer_id');
            $table->index('service_id', 'idx_appointments_service_id');

            $table->dropUnique(['staff_id', 'appointment_date', 'start_time']);

            // Bug 3 fix: end_time hata diya — same staff/date/start_time pe
            // do appointments allow nahi karne chahiye, duration kuch bhi ho
            $table->unique(
                ['staff_id', 'appointment_date', 'start_time'],
                'uq_staff_date_time_slot'
            );
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign('appointments_customer_id_foreign');
            $table->dropIndex('idx_appointments_customer_id');
            $table->foreign('customer_id')->references('id')->on('users')->cascadeOnDelete();

            $table->dropForeign('appointments_service_id_foreign');
            $table->dropIndex('idx_appointments_service_id');
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();

            $table->dropUnique('uq_staff_date_time_slot');

            $table->unique(
                ['staff_id', 'appointment_date', 'start_time'],
                'appointments_staff_id_appointment_date_start_time_unique'
            );
        });
    }
};
