<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `system_notifications` " .
            "MODIFY `priority` ENUM('high','normal','low') NOT NULL DEFAULT 'normal'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `system_notifications` " .
            "MODIFY `priority` ENUM('high','normal') NOT NULL DEFAULT 'normal'"
        );
    }
};
