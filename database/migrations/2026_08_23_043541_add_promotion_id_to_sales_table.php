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
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('promotionId')->nullable()->after('cashRegisterId');
            $table->foreign('promotionId')->references('id')->on('promotions')->nullOnDelete();
        });

        Schema::table('promotion_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('clientId')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['promotionId']);
            $table->dropColumn('promotionId');
        });

        Schema::table('promotion_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('clientId')->nullable(false)->change();
        });
    }
};
