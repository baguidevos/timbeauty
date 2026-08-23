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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('status', 30)->default('completed')->after('total');
            $table->integer('refundedQuantity')->default(0)->after('status');
            $table->string('cancelReason')->nullable()->after('refundedQuantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'refundedQuantity', 'cancelReason']);
        });
    }
};
