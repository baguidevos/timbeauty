<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saleId');
            $table->string('type'); // service, product
            $table->unsignedBigInteger('itemId')->nullable();
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('unitPrice', 12, 0);
            $table->decimal('discount', 12, 0)->default(0);
            $table->decimal('total', 12, 0);
            $table->timestamps();

            $table->foreign('saleId')->references('id')->on('sales')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
