<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('supplierId');
            $table->date('orderDate');
            $table->date('expectedDate')->nullable();
            $table->date('receivedDate')->nullable();
            $table->string('status')->default('pending'); // pending, approved, ordered, received, cancelled
            $table->decimal('totalAmount', 12, 0)->default(0);
            $table->decimal('paidAmount', 12, 0)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();

            $table->foreign('supplierId')->references('id')->on('suppliers')->cascadeOnDelete();
            $table->foreign('createdBy')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
