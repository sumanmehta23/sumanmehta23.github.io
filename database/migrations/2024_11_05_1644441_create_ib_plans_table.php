<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ib_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('ib_plan_id');
            $table->integer('ib_plan_cat_id');
            $table->integer('ib_acc_type_id');
            $table->decimal('ib_commission1', 10)->nullable();
            $table->decimal('ib_commission2', 10)->nullable();
            $table->decimal('ib_commission3', 10)->nullable();
            $table->decimal('ib_commission4', 10)->nullable();
            $table->decimal('ib_commission5', 10)->nullable();
            $table->decimal('ib_commission6', 10)->nullable();
            $table->decimal('ib_commission7', 10)->nullable();
            $table->decimal('ib_commission8', 10)->nullable();
            $table->decimal('ib_commission9', 10)->nullable();
            $table->decimal('ib_commission10', 10)->nullable();
            $table->decimal('ib_commission11', 10)->nullable();
            $table->decimal('ib_commission12', 10)->nullable();
            $table->decimal('ib_commission13', 10)->nullable();
            $table->decimal('ib_commission14', 10)->nullable();
            $table->decimal('ib_commission15', 10)->nullable();
            $table->boolean('status')->default(true);
            $table->string('updated_by');
            $table->integer('unique_c')->nullable()->storedAs('if((`status` = 1),`status`,NULL)');
            $table->unique(['ib_plan_cat_id', 'ib_acc_type_id', 'unique_c'], 'unique_ab_c');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib_plans');
    }
};
