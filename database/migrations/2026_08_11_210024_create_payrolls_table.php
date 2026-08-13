<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barberId');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('fixedSalary', 12, 0);
            $table->decimal('commissions', 12, 0)->default(0);
            $table->decimal('bonus', 12, 0)->default(0);
            $table->decimal('advances', 12, 0)->default(0);
            $table->decimal('deductions', 12, 0)->default(0);
            $table->decimal('netSalary', 12, 0);
            $table->string('status')->default('draft'); // draft, approved, paid, cancelled
            $table->timestamps();

            $table->foreign('barberId')->references('id')->on('barbers')->cascadeOnDelete();
            $table->index(['month', 'year']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
