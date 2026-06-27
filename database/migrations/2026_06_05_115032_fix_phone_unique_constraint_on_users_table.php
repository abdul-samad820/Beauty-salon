<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $hasOldIndex = ! empty(DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_phone_unique'"));
        $hasNewIndex = ! empty(DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_tenant_id_phone_unique'"));

        Schema::table('users', function (Blueprint $table) use ($hasOldIndex, $hasNewIndex) {
            if ($hasOldIndex) {
                $table->dropUnique(['phone']);
            }
            if (! $hasNewIndex) {
                $table->unique(['tenant_id', 'phone']);
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $indexes = DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_tenant_id_phone_unique'");

        Schema::table('users', function (Blueprint $table) use ($indexes) {
            if (! empty($indexes)) {
                $table->dropUnique(['tenant_id', 'phone']);
            }
            $table->unique(['phone']);
        });
    }
};
