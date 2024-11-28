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
        Schema::create('demo_deposit', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // $table->integer('id', true);
            $table->foreignIdFor(User::class)->constrained((new User())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Account::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('email', 50)->nullable();
            $table->string('trade_id', 100)->nullable();
            $table->string('deposit_amount', 100)->nullable();
            $table->string('deposit_type', 100)->nullable();
            $table->string('deposit_from', 100)->nullable();
            $table->timestamp('deposted_date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->integer('Status')->default(0);
            $table->string('AdminRemark', 100)->nullable();
            $table->string('Js_Admin_Remark_Date', 100)->nullable();
            $table->text('deposit_proof')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo_deposit');
    }
};
