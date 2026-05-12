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
        Schema::create("family_invite_codes", function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId("rental_booking_id")
                ->constrained()
                ->onDelete("cascade");

            $table
                ->foreignId("place_property_id")
                ->constrained("place_properties")
                ->onDelete("cascade");

            $table->string("code")->unique();

            $table->timestamp("expired_at")->nullable();
            $table->timestamp("used_at")->nullable();

            $table->boolean("is_used")->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("family_invite_codes");
    }
};
