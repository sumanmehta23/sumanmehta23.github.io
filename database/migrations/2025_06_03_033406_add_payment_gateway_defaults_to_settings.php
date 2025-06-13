<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add default settings if they don't already exist
        if (!DB::table('settings')->where('name', 'enable_cryptochill')->exists()) {
            DB::table('settings')->insert([
                'id' => Str::uuid()->toString(),
                'name' => 'enable_cryptochill',
                'value' => '1',
                'updated_at' => now(),
            ]);
        }

        if (!DB::table('settings')->where('name', 'enable_creditcardpayissa')->exists()) {
            DB::table('settings')->insert([
                'id' => Str::uuid()->toString(),
                'name' => 'enable_creditcardpayissa',
                'value' => '1',
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
