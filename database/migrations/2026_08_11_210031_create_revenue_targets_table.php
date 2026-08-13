<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_targets', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // shop, barber
            $table->unsignedBigInteger('barberId')->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('targetAmount', 12, 0);
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->timestamps();

            $table->foreign('barberId')->references('id')->on('barbers')->nullOnDelete();
            $table->foreign('createdBy')->references('id')->on('users')->nullOnDelete();
            $table->index(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_targets');
    }
};
