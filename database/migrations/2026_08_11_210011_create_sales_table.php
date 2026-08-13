<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clientId')->nullable();
            $table->unsignedBigInteger('barberId')->nullable();
            $table->decimal('subtotal', 12, 0);
            $table->decimal('discountAmount', 12, 0)->default(0);
            $table->decimal('total', 12, 0);
            $table->string('paymentMethod')->default('cash'); // cash, tmoney, flooz, card, transfer, other
            $table->string('status')->default('completed'); // completed, pending, refunded
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('cashRegisterId')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();

            $table->foreign('clientId')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('barberId')->references('id')->on('barbers')->nullOnDelete();
            $table->foreign('cashRegisterId')->references('id')->on('cash_registers')->nullOnDelete();
            $table->foreign('createdBy')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
