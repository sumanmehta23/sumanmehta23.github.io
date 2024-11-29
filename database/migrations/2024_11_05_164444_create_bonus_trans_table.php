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
        Schema::create('bonus_trans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class)->constrained((new User())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Account::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('email', 50)->nullable();
            $table->string('code', 100)->nullable();
            $table->string('bonus_amount', 100)->nullable();
            $table->string('bonus_currency', 50)->nullable()->default('USD');
            $table->string('bonus_type', 100)->nullable()->default('Entry');
            $table->string('bonus_code_id', 100)->nullable();
            $table->string('bonus_code_desc', 100)->nullable();
            $table->timestamp('bonus_date')->nullable()->useCurrent();
            $table->integer('status')->default(1);
            $table->string('admin_remark', 100)->nullable();
            $table->timestamp('Js_Admin_Remark_Date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_trans');
    }
};
