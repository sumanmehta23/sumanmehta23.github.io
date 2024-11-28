<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_withdraw', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 50)->nullable();
            $table->string('withdraw_amount', 100)->nullable();
            $table->string('withdraw_type', 100)->nullable();
            $table->string('company_bank', 100)->nullable();
            $table->string('client_bank', 100)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->timestamp('withdraw_date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('Status')->default(0);
            $table->longText('payout_req')->nullable();
            $table->longText('payout_res')->nullable();
            $table->string('admin_remark', 100)->nullable();
            $table->timestamp('Js_Admin_Remark_Date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('wallet_id', 100)->nullable();
            $table->string('wallet_qr', 500)->nullable();
            $table->text('client_note')->nullable();
            $table->foreignIdFor(User::class)->constrained((new User())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_withdraw');
    }
};
