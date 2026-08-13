<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clientId');
            $table->unsignedBigInteger('barberId');
            $table->unsignedBigInteger('serviceId');
            $table->date('date');
            $table->string('startTime'); // HH:mm
            $table->string('endTime'); // HH:mm
            $table->string('status')->default('pending'); // pending, confirmed, in_progress, completed, cancelled, no_show
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('clientId')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('barberId')->references('id')->on('barbers')->cascadeOnDelete();
            $table->foreign('serviceId')->references('id')->on('services')->cascadeOnDelete();
            $table->index(['date', 'barberId']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
