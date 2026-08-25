<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_advances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cashRegisterId')->nullable();
            $table->unsignedBigInteger('userId');
            $table->decimal('amount', 12, 0);
            $table->decimal('refundedAmount', 12, 0)->default(0);
            $table->string('status')->default('pending'); // pending, partially_refunded, refunded
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();

            $table->foreign('cashRegisterId')->references('id')->on('cash_registers')->nullOnDelete();
            $table->foreign('userId')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('createdBy')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_advances');
    }
};
