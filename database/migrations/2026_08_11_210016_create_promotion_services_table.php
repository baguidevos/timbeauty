<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promotionId');
            $table->unsignedBigInteger('serviceId');
            $table->timestamps();

            $table->foreign('promotionId')->references('id')->on('promotions')->cascadeOnDelete();
            $table->foreign('serviceId')->references('id')->on('services')->cascadeOnDelete();
            $table->unique(['promotionId', 'serviceId']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_services');
    }
};
