<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('budget_min', 15, 2)->nullable()->after('source');
            $table->decimal('budget_max', 15, 2)->nullable()->after('budget_min');
            $table->enum('preferred_contact', ['email', 'phone', 'text'])
                ->nullable()->after('budget_max');
            $table->enum('timeline', [
                'asap', '1_3_months', '3_6_months', '6_12_months', '12_plus_months',
            ])->nullable()->after('preferred_contact');
            $table->decimal('pre_approval_amount', 15, 2)->nullable()->after('timeline');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->text('lost_reason')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'budget_min', 'budget_max', 'preferred_contact',
                'timeline', 'pre_approval_amount',
            ]);
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('lost_reason');
        });
    }
};
