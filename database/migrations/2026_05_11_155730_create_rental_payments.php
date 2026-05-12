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
        Schema::create("rental_payments", function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId("rental_booking_id")
                ->constrained()
                ->onDelete("cascade");

            $table->decimal("amount", 12, 2);

            $table->string("payment_method")->nullable();
            $table->string("sender_name")->nullable();

            $table->text("notes")->nullable();

            $table->string("payment_proof");

            $table
                ->enum("status", ["pending", "approved", "rejected"])
                ->default("pending");

            $table
                ->foreignId("verified_by")
                ->nullable()
                ->constrained("users")
                ->nullOnDelete();

            $table->timestamp("verified_at")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("rental_payments");
    }
};
