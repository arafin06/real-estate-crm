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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['residential', 'commercial']);
            $table->enum('listing_type', ['sale', 'rent']);
            $table->decimal('price', 15, 2);
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip', 20);
            $table->unsignedSmallInteger('bedrooms')->nullable();
            $table->unsignedSmallInteger('bathrooms')->nullable();
            $table->unsignedInteger('area_sqft')->nullable();
            $table->enum('status', ['active', 'under_contract', 'sold', 'off_market'])->default('active');
            $table->index(['user_id', 'status']);
            $table->index('city');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
