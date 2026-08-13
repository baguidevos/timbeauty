<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clientId')->nullable();
            $table->string('type');
            $table->string('channel')->default('in_app'); // in_app, sms, whatsapp, email
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->boolean('sent')->default(false);
            $table->dateTime('sentAt')->nullable();
            $table->timestamps();

            $table->foreign('clientId')->references('id')->on('clients')->nullOnDelete();
            $table->index(['clientId', 'read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
