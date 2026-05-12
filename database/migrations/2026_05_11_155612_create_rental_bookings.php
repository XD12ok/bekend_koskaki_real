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
        Schema::create("rental_bookings", function (Blueprint $table) {
            $table->id();

            $table->foreignId("user_id")->constrained()->onDelete("cascade");

            $table
                ->foreignId("owner_id")
                ->constrained("users")
                ->onDelete("cascade");

            $table
                ->foreignId("booking_id")
                ->nullable()
                ->constrained()
                ->onDelete("set null");

            $table
                ->foreignId("place_property_id")
                ->constrained("place_properties")
                ->onDelete("cascade");

            $table->date("start_date");
            $table->date("end_date");

            $table->integer("duration");

            $table->enum("duration_type", ["week", "month", "year"]);

            $table->decimal("price_per_duration", 12, 2);
            $table->decimal("total_price", 12, 2);

            $table
                ->enum("status", [
                    "pending_payment",
                    "waiting_confirmation",
                    "active",
                    "rejected",
                    "expired",
                    "cancelled",
                    "renewal_pending",
                ])
                ->default("pending_payment");

            $table->boolean("family_joined")->default(false);

            $table->timestamp("approved_at")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("rental_bookings");
    }
};
