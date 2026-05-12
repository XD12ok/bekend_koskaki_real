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
        Schema::create("property_family_members", function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId("place_property_id")
                ->constrained("place_properties")
                ->onDelete("cascade");

            $table->foreignId("user_id")->constrained()->onDelete("cascade");

            $table
                ->foreignId("rental_booking_id")
                ->constrained()
                ->onDelete("cascade");

            $table->date("joined_at");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("property_family_members");
    }
};
