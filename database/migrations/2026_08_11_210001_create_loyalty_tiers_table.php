<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('minPoints');
            $table->decimal('pointsPerFCFA', 8, 4);
            $table->decimal('discountPercentage', 5, 2);
            $table->string('color');
            $table->string('icon')->nullable();
            $table->string('perks')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_tiers');
    }
};
