<?php

use App\Support\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('mobile');
            $table->string('phone', 10);
            $table->timestamps();
        });

        // Migrate existing single-phone values into the new table before dropping the column.
        DB::table('clients')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->select('id', 'phone')
            ->orderBy('id')
            ->get()
            ->each(function ($client) {
                $digits = PhoneNumber::normalize($client->phone);

                if ($digits !== null && strlen($digits) === 10) {
                    DB::table('client_phones')->insert([
                        'client_id' => $client->id,
                        'type' => 'mobile',
                        'phone' => $digits,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone')->nullable();
        });

        DB::table('client_phones')
            ->orderBy('client_id')
            ->orderBy('id')
            ->get()
            ->groupBy('client_id')
            ->each(function ($phones, $clientId) {
                DB::table('clients')->where('id', $clientId)->update([
                    'phone' => $phones->first()->phone,
                ]);
            });

        Schema::dropIfExists('client_phones');
    }
};
