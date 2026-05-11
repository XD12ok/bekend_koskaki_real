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
        Schema::create("place_properties", function (Blueprint $table) {
            $table->id();
            $table->string("title");
            $table->enum("tipe", ["putra", "putri", "campur"]);
            $table->string("tipe_kamar");
            $table->decimal("lebar", 10, 2);
            $table->decimal("panjang", 10, 2);
            $table->text("description");
            $table->bigInteger("price_perNight")->nullable();
            $table->bigInteger("price_perWeek")->nullable();
            $table->bigInteger("price_perMonth")->nullable();
            $table->bigInteger("price_perYear")->nullable();
            $table->text("address");
            $table->integer("city_id");
            $table->integer("max_people");
            $table->enum("status", ["active", "inactive"]);
            $table->float("rating_avg")->default(0);
            $table->integer("rating_count")->default(0);
            $table->timestamp("created_at");
            $table->timestamp("updated_at")->nullable();

            //foreign key
            $table
                ->foreignId("owner_id")
                ->constrained("users")
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
