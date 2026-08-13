<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payrollId');
            $table->unsignedBigInteger('barberId');
            $table->decimal('amount', 12, 0);
            $table->date('date');
            $table->string('method')->default('cash');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('payrollId')->references('id')->on('payrolls')->cascadeOnDelete();
            $table->foreign('barberId')->references('id')->on('barbers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
