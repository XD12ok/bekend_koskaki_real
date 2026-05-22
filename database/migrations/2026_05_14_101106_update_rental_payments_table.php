<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table("rental_payments", function (Blueprint $table) {
            // relasi ke invoice
            $table
                ->foreignId("invoice_id")
                ->nullable()
                ->after("rental_booking_id")
                ->constrained()
                ->onDelete("cascade");

            // nominal yg diinput tenant
            $table
                ->decimal("claimed_amount", 12, 2)
                ->nullable()
                ->after("invoice_id");

            // nominal valid hasil verifikasi owner
            $table
                ->decimal("verified_amount", 12, 2)
                ->nullable()
                ->after("claimed_amount");
        });
    }

    public function down(): void
    {
        Schema::table("rental_payments", function (Blueprint $table) {
            $table->dropConstrainedForeignId("invoice_id");

            $table->dropColumn(["claimed_amount", "verified_amount"]);
        });
    }
};
