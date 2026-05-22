<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('place_properties', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('google_maps_link')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('place_properties', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'google_maps_link'
            ]);
        });
    }
};
