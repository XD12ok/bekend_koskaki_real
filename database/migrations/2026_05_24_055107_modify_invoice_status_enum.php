<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // hapus constraint lama
        DB::statement("
            ALTER TABLE invoices
            DROP CONSTRAINT invoices_status_check
        ");

        // buat constraint baru
        DB::statement("
            ALTER TABLE invoices
            ADD CONSTRAINT invoices_status_check
            CHECK (
                status IN (
                    'unpaid',
                    'partial',
                    'paid',
                    'overdue',
                    'grace'
                )
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE invoices
            DROP CONSTRAINT invoices_status_check
        ");

        DB::statement("
            ALTER TABLE invoices
            ADD CONSTRAINT invoices_status_check
            CHECK (
                status IN (
                    'unpaid',
                    'partial',
                    'paid',
                    'overdue'
                )
            )
        ");
    }
};
