<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 0);
            $table->integer('duration');
            $table->unsignedBigInteger('categoryId')->nullable();
            $table->decimal('commissionRate', 5, 2)->default(0);
            $table->string('status')->default('active'); // active, inactive
            $table->string('photo')->nullable();
            $table->timestamps();

            $table->foreign('categoryId')->references('id')->on('service_categories')->nullOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
