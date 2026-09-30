<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('expected_close_date');
            $table->index('closed_at');
        });

        // Backfill existing closed deals — best estimate is updated_at
        DB::statement("
            UPDATE deals
            SET closed_at = updated_at
            WHERE stage = 'closed' AND closed_at IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex(['closed_at']);
            $table->dropColumn('closed_at');
        });
    }
};
