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
        Schema::create("kost_features", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("place_properties_id")
                ->constrained()
                ->cascadeOnDelete();
            $table->string("feature");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("kost_feature");
    }
};
