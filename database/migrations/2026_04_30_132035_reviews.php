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
        Schema::create("reviews", function (Blueprint $table) {
            $table->id();

            $table->foreignId("user_id")->constrained()->cascadeOnDelete();

            $table
                ->foreignId("place_properties_id")
                ->constrained()
                ->cascadeOnDelete();

            $table->tinyInteger("rating"); // 1 - 5

            $table->text("comment")->nullable();

            $table->timestamps();

            // biar 1 user cuma bisa review 1x per kos
            $table->unique(["user_id", "place_properties_id"]);
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
