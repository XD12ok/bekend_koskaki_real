<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("booking_reschedule_requests", function (
            Blueprint $table,
        ) {
            $table->id();

            $table->foreignId("booking_id")->constrained()->cascadeOnDelete();

            $table
                ->foreignId("requested_by")
                ->constrained("users")
                ->cascadeOnDelete();

            $table->date("old_visit_date");
            $table->time("old_visit_time");

            $table->date("new_visit_date");
            $table->time("new_visit_time");

            $table->text("reason")->nullable();

            $table->boolean("approved_by_user")->default(false);

            $table->boolean("approved_by_owner")->default(false);

            $table
                ->enum("status", [
                    "pending",
                    "accepted",
                    "rejected",
                    "cancelled",
                ])
                ->default("pending");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("booking_reschedule_requests");
    }
};
