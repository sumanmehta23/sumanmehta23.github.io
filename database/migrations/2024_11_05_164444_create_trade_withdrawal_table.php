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
        Schema::create('trade_withdrawal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class)->constrained((new User())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Account::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Account::class, 'to_account_id')->nullable()->constrained('accounts')->onUpdate('cascade')->onDelete('cascade');
            // $table->uuid('user_id');
            $table->string('email', 50)->nullable();
            // $table->uuid('account_id')->nullable();
            $table->string('withdrawal_amount', 100)->nullable();
            $table->string('withdraw_type', 100)->nullable();
            // $table->string('withdraw_to', 100)->nullable();
            $table->string('wallet_qr', 250)->nullable();
            $table->timestamp('withdraw_date')->nullable()->useCurrent();
            $table->integer('Status')->default(0);
            $table->string('admin_remark', 100)->nullable();
            $table->string('Js_Admin_Remark_Date', 100)->nullable();
            $table->string('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_withdrawal');
    }
};
