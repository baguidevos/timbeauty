<?php

use App\Models\Client;
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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
        });

        // Generate client codes for existing clients
        foreach (Client::cursor() as $client) {
            if (empty($client->code)) {
                $client->code = Client::generateClientCode(
                    firstName: $client->firstName,
                    phone: $client->phone ?: $client->whatsapp,
                    id: $client->id,
                );
                $client->saveQuietly();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
