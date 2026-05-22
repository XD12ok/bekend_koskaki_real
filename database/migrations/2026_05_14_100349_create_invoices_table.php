<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("invoices", function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId("rental_booking_id")
                ->constrained()
                ->onDelete("cascade");

            // periode tagihan
            $table->date("period_start");
            $table->date("period_end");

            // jatuh tempo
            $table->date("due_date");

            // total tagihan
            $table->decimal("total_amount", 12, 2);

            // total sudah dibayar
            $table->decimal("paid_amount", 12, 2)->default(0);

            // sisa pembayaran
            $table->decimal("remaining_amount", 12, 2);

            $table
                ->enum("status", ["unpaid", "partial", "paid", "overdue"])
                ->default("unpaid");

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("invoices");
    }
};
