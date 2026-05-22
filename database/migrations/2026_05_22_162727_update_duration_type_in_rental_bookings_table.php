<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // hapus constraint lama
        DB::statement("
            ALTER TABLE rental_bookings
            DROP CONSTRAINT rental_bookings_duration_type_check
        ");

        // buat constraint baru
        DB::statement("
            ALTER TABLE rental_bookings
            ADD CONSTRAINT rental_bookings_duration_type_check
            CHECK (
                duration_type IN (
                    'night',
                    'week',
                    'month',
                    'year'
                )
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE rental_bookings
            DROP CONSTRAINT rental_bookings_duration_type_check
        ");

        DB::statement("
            ALTER TABLE rental_bookings
            ADD CONSTRAINT rental_bookings_duration_type_check
            CHECK (
                duration_type IN (
                    'week',
                    'month',
                    'year'
                )
            )
        ");
    }
};
