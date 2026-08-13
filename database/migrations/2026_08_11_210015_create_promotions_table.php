<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // percentage, fixed_amount
            $table->decimal('value', 12, 0);
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->integer('minVisits')->nullable();
            $table->boolean('forLoyalOnly')->default(false);
            $table->integer('maxUsages')->nullable();
            $table->integer('currentUsages')->default(0);
            $table->string('status')->default('active'); // active, inactive, expired
            $table->timestamps();

            $table->index('status');
            $table->index(['startDate', 'endDate']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
