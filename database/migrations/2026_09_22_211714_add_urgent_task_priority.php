<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority
            ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium'");
    }

    public function down(): void
    {
        // MySQL truncates rows holding a value the narrowed enum no longer allows,
        // so fold urgent back into high before shrinking the column.
        DB::statement("UPDATE tasks SET priority = 'high' WHERE priority = 'urgent'");

        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority
            ENUM('low','medium','high') NOT NULL DEFAULT 'medium'");
    }
};
