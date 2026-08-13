<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('requiredVisits');
            $table->unsignedBigInteger('serviceId')->nullable();
            $table->decimal('discountPercentage', 5, 2);
            $table->string('message')->nullable();
            $table->integer('cooldownDays')->default(0);
            $table->integer('validityDays')->default(30);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->foreign('serviceId')->references('id')->on('services')->nullOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_rules');
    }
};
