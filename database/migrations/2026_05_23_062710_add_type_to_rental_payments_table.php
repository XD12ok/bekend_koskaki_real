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
        Schema::table("rental_payments", function (Blueprint $table) {
            $table
                ->enum("type", ["initial", "monthly"])
                ->default("monthly")
                ->after("amount");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("rental_payments", function (Blueprint $table) {
            $table->dropColumn("type");
        });
    }
};
