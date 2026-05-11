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
        Schema::create("property_images", function (Blueprint $table) {
            $table->id();
            $table->string("url");
            $table->boolean("is_main");

            //foreign key
            $table
                ->foreignId("properties_id")
                ->constrained("place_properties")
                ->onDelete("cascade");

            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
