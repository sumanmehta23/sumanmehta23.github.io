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
        Schema::table('aspnetusers', function (Blueprint $table) {
            $table->string('klaviyo_id')->nullable()->after('wallet_approved_at');
            $table->json('klaviyo_last_error')->nullable()->after('klaviyo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aspnetusers', function (Blueprint $table) {
            $table->dropColumns(['klaviyo_last_error','klaviyo_id']);
        });
    }
};
