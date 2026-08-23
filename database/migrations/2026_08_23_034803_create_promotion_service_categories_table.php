<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotion_service_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotionId');
            $table->unsignedBigInteger('categoryId');
            $table->timestamps();

            $table->foreign('promotionId')->references('id')->on('promotions')->cascadeOnDelete();
            $table->foreign('categoryId')->references('id')->on('service_categories')->cascadeOnDelete();
            $table->unique(['promotionId', 'categoryId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_service_categories');
    }
};
