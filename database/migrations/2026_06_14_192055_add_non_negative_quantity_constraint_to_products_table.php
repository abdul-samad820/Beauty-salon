<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // DB-level safety net — prevents quantity going negative via ANY path
        // (AppointmentObserver direct decrement bypasses application-level guards)
        //
        // SQLite does NOT support ALTER TABLE ... ADD CONSTRAINT syntax,
        // so this runs only on MySQL/MariaDB (production).
        // SQLite enforces this at the application layer instead.
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE products ADD CONSTRAINT chk_quantity_non_negative CHECK (quantity >= 0)'
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE products DROP CONSTRAINT chk_quantity_non_negative'
            );
        }
    }
};
