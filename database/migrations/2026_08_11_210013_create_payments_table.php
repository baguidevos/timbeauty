<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saleId')->nullable();
            $table->unsignedBigInteger('clientId')->nullable();
            $table->decimal('amount', 12, 0);
            $table->string('method');
            $table->string('reference')->nullable();
            $table->string('status')->default('completed'); // pending, completed, failed
            $table->timestamps();

            $table->foreign('saleId')->references('id')->on('sales')->nullOnDelete();
            $table->foreign('clientId')->references('id')->on('clients')->nullOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
