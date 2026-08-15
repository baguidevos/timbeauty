<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('barbers', function (Blueprint $table) {
            $table->string('jobTitle')->default('barber')->after('status'); // barber, manager, receptionist, cashier, cleaner, other
            $table->boolean('canPerformServices')->default(true)->after('jobTitle');
            $table->index('jobTitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barbers', function (Blueprint $table) {
            $table->dropIndex(['jobTitle']);
            $table->dropColumn(['jobTitle', 'canPerformServices']);
        });
    }
};
