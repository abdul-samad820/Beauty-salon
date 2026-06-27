<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['appointment_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign('reviews_appointment_id_foreign');
            $table->dropUnique(['appointment_id', 'customer_id']);
            $table->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
        });
    }
};
