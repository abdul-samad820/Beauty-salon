<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('user_id');
            $table->string('type')->default('system')->after('action'); // 'booking', 'review', 'stock', 'payment', 'subscription'
            $table->index(['tenant_id', 'is_read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'type']);
        });
    }
};
