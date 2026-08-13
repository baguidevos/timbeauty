<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->decimal('openingAmount', 12, 0);
            $table->decimal('closingAmount', 12, 0)->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->dateTime('openedAt');
            $table->dateTime('closedAt')->nullable();
            $table->unsignedBigInteger('openedBy')->nullable();
            $table->timestamps();

            $table->foreign('openedBy')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
