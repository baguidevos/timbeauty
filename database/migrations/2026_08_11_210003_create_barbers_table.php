<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('hireDate')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->string('specialties')->nullable();
            $table->string('photo')->nullable();
            $table->string('remunerationType')->default('fixed'); // fixed, commission, fixed_plus_commission, per_service, hybrid
            $table->decimal('fixedSalary', 12, 0)->default(0);
            $table->decimal('commissionRate', 5, 2)->default(0);
            $table->decimal('perServiceRate', 12, 0)->default(0);
            $table->unsignedBigInteger('userId')->nullable()->unique();
            $table->timestamps();

            $table->foreign('userId')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barbers');
    }
};
