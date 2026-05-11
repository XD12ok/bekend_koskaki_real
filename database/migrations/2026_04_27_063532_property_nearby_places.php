<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("property_nearby_places", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("place_property_id")
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId("place_id")->constrained()->cascadeOnDelete();
            $table->decimal("distance", 8, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
