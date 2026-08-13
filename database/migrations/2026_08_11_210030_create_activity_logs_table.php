<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId')->nullable();
            $table->string('action');
            $table->string('entity')->nullable();
            $table->unsignedBigInteger('entityId')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->foreign('userId')->references('id')->on('users')->nullOnDelete();
            $table->index(['entity', 'entityId']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
