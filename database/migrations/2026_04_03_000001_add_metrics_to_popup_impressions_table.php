<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popup_impressions', function (Blueprint $table) {
            $table->timestamp('dismissed_at')->nullable()->after('shown_at');
            $table->timestamp('cta_clicked_at')->nullable()->after('dismissed_at');
        });
    }

    public function down(): void
    {
        Schema::table('popup_impressions', function (Blueprint $table) {
            $table->dropColumn(['dismissed_at', 'cta_clicked_at']);
        });
    }
};
