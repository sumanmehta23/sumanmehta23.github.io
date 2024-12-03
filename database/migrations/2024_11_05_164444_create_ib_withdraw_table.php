<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Ib1;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ib_withdraw', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Ib1::class)->constrained((new Ib1())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('w_index');
            $table->string('uid', 150);
            $table->integer('orderId')->nullable();
            $table->dateTime('date')->nullable();
            $table->string('gateway', 150)->nullable();
            $table->string('currency', 5)->default('USD');
            $table->double('amount')->default(0);
            $table->string('status', 25)->default('pending');
            $table->string('ib_name', 150);
            $table->string('ib_email', 150);
            $table->string('processed_by', 150)->nullable();
            $table->dateTime('processed_on')->nullable();
            $table->mediumText('comment_by_agent')->nullable();
            $table->mediumText('withdraw_details')->nullable();
            $table->dateTime('cancel_date')->nullable();
            $table->string('invoice_image')->nullable();
            $table->string('invoice_portal', 50)->default('admin');
            $table->dateTime('upload_date')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->double('updated_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib_withdraw');
    }
};
