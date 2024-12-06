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
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class)->constrained((new User())->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\AccountType::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('demo')->default(true);
            $table->string('name')->nullable();//missing
            $table->string('email', 50)->nullable();
            $table->string('code', 50)->nullable();
            $table->decimal('credit', 10)->nullable();
            $table->string('leverage');
            $table->string('currency', 20)->default('USD');
            $table->decimal('balance', 15)->default(0);
            $table->double('equity')->nullable()->default(0);
            $table->string('trade_platform', 100)->default('MetaTrader5');
            $table->integer('lots_completed')->default(0);
            $table->double('margin_free')->default(0);
            $table->double('margin_level')->default(0);
            $table->string('margin_level_type')->default('ok');
            $table->double('adjustment')->default(0);
            $table->double('deposit')->default(0);
            $table->double('withdraw')->default(0);
            $table->double('internal_transfer')->default(0);
            $table->double('internal_deposit')->default(0);
            $table->text('trader_password')->nullable();
            $table->text('invester_password')->nullable();
            $table->text('phone_password')->nullable();
            $table->timestamp('registered_date')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->string('status', 50)->default('active');
            $table->double('bonus_deposit')->default(0);
            $table->double('w_bonus_deposit')->default(0);
            $table->string('ib1', 100)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
