<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('gender')->nullable();
            $table->date('birthDate')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->date('firstVisitDate');
            $table->date('lastVisitDate')->nullable();
            $table->integer('totalVisits')->default(0);
            $table->decimal('totalSpent', 12, 0)->default(0);
            $table->boolean('isLoyal')->default(false);
            $table->integer('loyaltyPoints')->default(0);
            $table->unsignedBigInteger('loyaltyTierId')->nullable();
            $table->timestamps();

            $table->foreign('loyaltyTierId')->references('id')->on('loyalty_tiers')->nullOnDelete();
            $table->index('phone');
            $table->index('lastName');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
