<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("bookings", function (Blueprint $table) {
            $table->id();

            $table->foreignId("user_id")->constrained()->cascadeOnDelete();

            $table
                ->foreignId("place_properties_id")
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->foreignId("owner_id")
                ->constrained("users")
                ->cascadeOnDelete();

            $table->date("visit_date");

            $table->time("visit_time");

            $table
                ->enum("status", [
                    "pending",
                    "accepted",
                    "rejected",
                    "completed",
                    "cancelled",
                ])
                ->default("pending");

            $table->text("notes")->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("bookings");
    }
};
