<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoryId')->nullable();
            $table->decimal('amount', 12, 0);
            $table->date('date');
            $table->string('description')->nullable();
            $table->string('beneficiary')->nullable();
            $table->string('paymentMethod')->default('cash');
            $table->string('receipt')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('cashRegisterId')->nullable();
            $table->timestamps();

            $table->foreign('categoryId')->references('id')->on('expense_categories')->nullOnDelete();
            $table->foreign('createdBy')->references('id')->on('users')->nullOnDelete();
            $table->foreign('cashRegisterId')->references('id')->on('cash_registers')->nullOnDelete();
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
