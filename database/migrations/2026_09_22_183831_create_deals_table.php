<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('stage', [
                'lead', 'prospect', 'showing',
                'offer_made', 'under_contract', 'closed', 'lost',
            ])->default('lead');
            $table->decimal('deal_value', 15, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(3.00);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->date('expected_close_date')->nullable();
            $table->text('notes')->nullable();
            $table->index(['user_id', 'stage']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
