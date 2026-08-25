<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('cashRegisterId')->nullable();
            $table->decimal('amount', 12, 0);
            $table->string('bankName');
            $table->string('bankAccountNumber')->nullable();
            $table->string('depositSlipNumber')->nullable();
            $table->string('depositSlipPhoto')->nullable();
            $table->unsignedBigInteger('depositedBy')->nullable();
            $table->string('status')->default('confirmed'); // pending, confirmed, cancelled
            $table->datetime('depositDate');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();

            $table->foreign('cashRegisterId')->references('id')->on('cash_registers')->nullOnDelete();
            $table->foreign('depositedBy')->references('id')->on('users')->nullOnDelete();
            $table->foreign('createdBy')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
            $table->index('depositDate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_deposits');
    }
};
