<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_absences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barberId');
            $table->date('startDate');
            $table->date('endDate');
            $table->string('reason')->nullable();
            $table->string('type')->default('personal'); // sick, personal, holiday
            $table->timestamps();

            $table->foreign('barberId')->references('id')->on('barbers')->cascadeOnDelete();
            $table->index('startDate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_absences');
    }
};
