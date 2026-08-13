<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clientId');
            $table->integer('points');
            $table->string('type'); // earn, redeem, adjust, bonus
            $table->string('description')->nullable();
            $table->unsignedBigInteger('saleId')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->foreign('clientId')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('saleId')->references('id')->on('sales')->nullOnDelete();
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_point_transactions');
    }
};
