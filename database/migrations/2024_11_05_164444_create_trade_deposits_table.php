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
        Schema::create('trade_deposits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(\App\Models\Account::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignIdFor(User::class)->constrained((new User())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->string('email', 50)->nullable();
            $table->string('code', 100)->nullable();
            $table->string('deposit_amount', 100)->nullable();
            $table->string('deposit_currency', 50)->nullable()->default('USD');
            $table->string('deposit_type', 100)->nullable();
            $table->string('deposit_from', 100)->nullable();
            $table->timestamp('deposted_date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('status')->default(0);
            $table->string('admin_remark', 100)->nullable();
            $table->timestamp('Js_Admin_Remark_Date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->text('deposit_proof')->nullable();
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
        Schema::dropIfExists('trade_deposit');
    }
};
