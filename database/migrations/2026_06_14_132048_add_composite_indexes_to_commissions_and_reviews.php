<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->index(['tenant_id', 'staff_id'], 'idx_commissions_tenant_staff');
            $table->index(['tenant_id', 'status'], 'idx_commissions_tenant_status');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'idx_reviews_tenant_status');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('commissions') && $this->indexExists('commissions', 'idx_commissions_tenant_staff')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->dropIndex('idx_commissions_tenant_staff');
            });
        }

        if (Schema::hasTable('commissions') && $this->indexExists('commissions', 'idx_commissions_tenant_status')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->dropIndex('idx_commissions_tenant_status');
            });
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign('reviews_tenant_id_foreign');
            $table->dropIndex('idx_reviews_tenant_status');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return count($result) > 0;
    }
};
