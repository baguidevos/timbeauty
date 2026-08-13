<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointmentId')->nullable();
            $table->unsignedBigInteger('clientId');
            $table->unsignedBigInteger('barberId')->nullable();
            $table->string('type'); // before, after
            $table->string('url');
            $table->string('caption')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();

            $table->foreign('appointmentId')->references('id')->on('appointments')->nullOnDelete();
            $table->foreign('clientId')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('barberId')->references('id')->on('barbers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_photos');
    }
};
