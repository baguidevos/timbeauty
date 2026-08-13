<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('productId');
            $table->string('type'); // in, out
            $table->integer('quantity');
            $table->string('reason')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->foreign('productId')->references('id')->on('products')->cascadeOnDelete();
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
