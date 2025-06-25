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
        Schema::table('accounts', function (Blueprint $table) {
            $table->renameColumn('competition_month', 'competition_start_date');
            $table->renameColumn('competition_year', 'competition_end_date');
        });
        Schema::table('accounts', function (Blueprint $table) {
            $table->date('competition_start_date')->nullable()->change();
            $table->date('competition_end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('competition_start_date')->change(); // assuming old type was string
            $table->string('competition_end_date')->change();   // assuming old type was string
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->renameColumn('competition_start_date', 'competition_month');
            $table->renameColumn('competition_end_date', 'competition_year');
        });
    }
};
