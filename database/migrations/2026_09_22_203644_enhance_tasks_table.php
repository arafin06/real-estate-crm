<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL rejects an UPDATE to a value the enum does not yet allow, so widen
        // the enum to hold both vocabularies, remap the rows, then narrow it.
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status
            ENUM('pending','in_progress','completed','incomplete','complete','closed')
            NOT NULL DEFAULT 'pending'");

        DB::statement("UPDATE tasks SET status = 'incomplete' WHERE status IN ('pending', 'in_progress')");
        DB::statement("UPDATE tasks SET status = 'complete'   WHERE status = 'completed'");

        DB::statement("ALTER TABLE tasks MODIFY COLUMN status
            ENUM('incomplete','complete','closed') NOT NULL DEFAULT 'incomplete'");

        Schema::table('tasks', function (Blueprint $table) {
            // Subtasks
            $table->unsignedBigInteger('parent_id')->nullable()->after('user_id');
            $table->foreign('parent_id')->references('id')->on('tasks')->nullOnDelete();

            // Recurring
            $table->boolean('is_recurring')->default(false)->after('status');
            $table->enum('recurrence_type', ['daily', 'weekly', 'biweekly', 'monthly', 'yearly'])
                ->nullable()->after('is_recurring');
            $table->unsignedTinyInteger('recurrence_interval')->default(1)->after('recurrence_type');
            $table->json('recurrence_days')->nullable()->after('recurrence_interval'); // [1,3,5] = Mon/Wed/Fri
            $table->date('recurrence_end_date')->nullable()->after('recurrence_days');
            $table->unsignedBigInteger('recurring_parent_id')->nullable()->after('recurrence_end_date');
            $table->foreign('recurring_parent_id')->references('id')->on('tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['recurring_parent_id']);
            $table->dropColumn([
                'parent_id', 'is_recurring', 'recurrence_type', 'recurrence_interval',
                'recurrence_days', 'recurrence_end_date', 'recurring_parent_id',
            ]);
        });

        DB::statement("ALTER TABLE tasks MODIFY COLUMN status
            ENUM('pending','in_progress','completed','incomplete','complete','closed')
            NOT NULL DEFAULT 'incomplete'");

        DB::statement("UPDATE tasks SET status = 'pending'   WHERE status = 'incomplete'");
        DB::statement("UPDATE tasks SET status = 'completed' WHERE status = 'complete'");
        // 'closed' has no pre-existing equivalent; fold it into 'completed' so the
        // enum change below cannot reject or blank out those rows.
        DB::statement("UPDATE tasks SET status = 'completed' WHERE status = 'closed'");

        DB::statement("ALTER TABLE tasks MODIFY COLUMN status
            ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending'");
    }
};
