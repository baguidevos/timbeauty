<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barberId');
            $table->integer('dayOfWeek'); // 0-6
            $table->string('startTime'); // HH:mm
            $table->string('endTime'); // HH:mm
            $table->boolean('isDayOff')->default(false);
            $table->timestamps();

            $table->foreign('barberId')->references('id')->on('barbers')->cascadeOnDelete();
            $table->index(['barberId', 'dayOfWeek']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_schedules');
    }
};
