<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('categoryId')->nullable();
            $table->string('brand')->nullable();
            $table->decimal('purchasePrice', 12, 0)->default(0);
            $table->decimal('sellingPrice', 12, 0);
            $table->integer('stockQuantity')->default(0);
            $table->integer('minStockLevel')->default(5);
            $table->string('supplier')->nullable();
            $table->unsignedBigInteger('supplierId')->nullable();
            $table->string('photo')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->foreign('categoryId')->references('id')->on('product_categories')->nullOnDelete();
            $table->foreign('supplierId')->references('id')->on('suppliers')->nullOnDelete();
            $table->index('status');
            $table->index('stockQuantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
