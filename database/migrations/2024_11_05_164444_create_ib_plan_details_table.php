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
        Schema::create('ib_plan_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
           
            $table->foreignIdFor(\App\Models\IbCategory::class)->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignIdFor(\App\Models\AccountType::class)->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->integer('acc_type');
            $table->integer('level_id');
            $table->decimal('d1', 10)->nullable()->default(0);
            $table->decimal('d2', 10)->nullable()->default(0);
            $table->decimal('d3', 10)->nullable()->default(0);
            $table->double('d4', 10, 2)->nullable()->default(0);
            $table->decimal('d5', 10)->nullable()->default(0);
            $table->decimal('d6', 10)->nullable()->default(0);
            $table->decimal('d7', 10)->nullable()->default(0);
            $table->decimal('d8', 10)->nullable()->default(0);
            $table->decimal('d9', 10)->nullable()->default(0);
            $table->decimal('d10', 10)->nullable()->default(0);
            $table->decimal('d11', 10)->nullable()->default(0);
            $table->decimal('d12', 10)->nullable()->default(0);
            $table->decimal('d13', 10)->nullable()->default(0);
            $table->decimal('d14', 10)->nullable()->default(0);
            $table->decimal('d15', 10)->nullable()->default(0);
            $table->tinyInteger('status')->default(1);
            $table->string('updated_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ib_plan_details');
    }
};
