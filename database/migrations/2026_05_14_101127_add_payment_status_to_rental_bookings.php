<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table("rental_bookings", function (Blueprint $table) {
            $table->date("next_payment_date")->nullable();

            $table->date("grace_until")->nullable();

            $table
                ->enum("payment_status", [
                    "active",
                    "partial",
                    "overdue",
                    "grace",
                    "expired",
                ])
                ->default("active");
        });
    }

    public function down(): void
    {
        Schema::table("rental_bookings", function (Blueprint $table) {
            $table->dropColumn([
                "next_payment_date",
                "grace_until",
                "payment_status",
            ]);
        });
    }
};
