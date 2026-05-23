<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table("invoices", function (Blueprint $table) {
            $table->enum("type", ["initial", "monthly"])->default("monthly");
        });
    }

    public function down(): void
    {
        Schema::table("invoices", function (Blueprint $table) {
            $table->dropColumn("type");
        });
    }
};
