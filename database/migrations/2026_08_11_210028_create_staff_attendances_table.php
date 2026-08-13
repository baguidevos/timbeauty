<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barberId');
            $table->date('date');
            $table->dateTime('clockIn')->nullable();
            $table->dateTime('clockOut')->nullable();
            $table->string('status')->default('present'); // present, late, absent, half_day, holiday
            $table->string('notes')->nullable();
            $table->integer('workedMinutes')->default(0);
            $table->integer('breakMinutes')->default(0);
            $table->timestamps();

            $table->foreign('barberId')->references('id')->on('barbers')->cascadeOnDelete();
            $table->index(['barberId', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
