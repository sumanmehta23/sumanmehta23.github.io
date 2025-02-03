<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('ib1', function (Blueprint $table) {
            $table->dropColumn('indexId'); // Drop existing column
        });

        Schema::table('ib1', function (Blueprint $table) {
            $table->id('indexId')->unsigned(); // Recreate as unsigned auto-increment
        });
    }

    public function down()
    {
        Schema::table('ib1', function (Blueprint $table) {
            $table->dropColumn('indexId'); // Drop new column
        });

        Schema::table('ib1', function (Blueprint $table) {
            $table->bigInteger('indexId')->default(0); // Revert to original type
        });
    }
};
