<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RentalPayment;

class RentalPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RentalPayment::create([
            "id" => 1,
            "type" => "initial",
            "total_amount" => 500000,
            "paid_amount" => 250000,
            "remaining_amount" => 250000,
            "status" => "partial",
        ]);
    }
}
