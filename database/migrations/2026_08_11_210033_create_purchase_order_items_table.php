<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchaseOrderId');
            $table->unsignedBigInteger('productId')->nullable();
            $table->string('productName');
            $table->integer('quantity');
            $table->decimal('unitPrice', 12, 0);
            $table->decimal('totalPrice', 12, 0);
            $table->integer('receivedQuantity')->default(0);
            $table->timestamps();

            $table->foreign('purchaseOrderId')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('productId')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
