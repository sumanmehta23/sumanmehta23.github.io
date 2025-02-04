<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ib1', function (Blueprint $table) {
            DB::statement("ALTER TABLE ib1 MODIFY indexId INT UNSIGNED;");

            $rows = DB::table('ib1')->get();
            foreach ($rows as $row) {
                $randomNumber = random_int(100000, 999999); // Generate a unique random number
                DB::table('ib1')->where('id', $row->id)->update(['indexId' => $randomNumber]);
            }
            DB::statement("ALTER TABLE ib1 ADD UNIQUE (indexId);");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ib1', function (Blueprint $table) {
            DB::statement("ALTER TABLE ib1 DROP INDEX indexId;");
            // Revert column type to BIGINT
            DB::statement("ALTER TABLE ib1 MODIFY indexId BIGINT;");
        });
    }
};
