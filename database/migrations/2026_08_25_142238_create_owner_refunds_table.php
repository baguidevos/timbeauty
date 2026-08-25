<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ownerAdvanceId');
            $table->unsignedBigInteger('cashRegisterId')->nullable();
            $table->decimal('amount', 12, 0);
            $table->string('paymentMethod')->default('cash'); // cash, transfer, check
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();

            $table->foreign('ownerAdvanceId')->references('id')->on('owner_advances')->cascadeOnDelete();
            $table->foreign('cashRegisterId')->references('id')->on('cash_registers')->nullOnDelete();
            $table->foreign('createdBy')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_refunds');
    }
};
