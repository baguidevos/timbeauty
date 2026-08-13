<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotionId');
            $table->unsignedBigInteger('clientId');
            $table->unsignedBigInteger('saleId')->nullable();
            $table->timestamps();

            $table->foreign('promotionId')->references('id')->on('promotions')->cascadeOnDelete();
            $table->foreign('clientId')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('saleId')->references('id')->on('sales')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_usages');
    }
};
