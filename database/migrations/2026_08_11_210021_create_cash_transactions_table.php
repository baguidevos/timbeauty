<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cashRegisterId');
            $table->string('type'); // sale, expense, deposit, withdrawal, adjustment
            $table->decimal('amount', 12, 0);
            $table->string('description')->nullable();
            $table->string('referenceId')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();

            $table->foreign('cashRegisterId')->references('id')->on('cash_registers')->cascadeOnDelete();
            $table->foreign('createdBy')->references('id')->on('users')->nullOnDelete();
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
