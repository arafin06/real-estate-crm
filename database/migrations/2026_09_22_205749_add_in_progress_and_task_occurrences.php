<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add in_progress to status enum
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status
            ENUM('incomplete','in_progress','complete','closed') NOT NULL DEFAULT 'incomplete'");

        // Recurring occurrence history
        Schema::create('task_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('due_date'); // due_date active when this occurrence was completed
            $table->text('note')->nullable();
            $table->timestamp('completed_at');
            $table->timestamps();
            $table->index(['task_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_occurrences');

        // MySQL truncates rows holding a value the narrowed enum no longer allows,
        // so fold in_progress back into incomplete before shrinking the column.
        DB::statement("UPDATE tasks SET status = 'incomplete' WHERE status = 'in_progress'");

        DB::statement("ALTER TABLE tasks MODIFY COLUMN status
            ENUM('incomplete','complete','closed') NOT NULL DEFAULT 'incomplete'");
    }
};
