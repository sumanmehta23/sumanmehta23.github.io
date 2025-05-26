<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->string('account_code');
            $table->decimal('equity', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->date('report_date');
            $table->timestamps();

            $table->foreign('account_code')
                  ->references('code')
                  ->on('accounts')
                  ->onDelete('cascade');

            $table->index(['account_code', 'report_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_reports');
    }
};
