<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        "rental_booking_id",

        "period_start",
        "period_end",

        "due_date",

        "total_amount",

        // harga asli sebelum denda/grace
        "original_amount",

        "last_penalty_at",

        "paid_amount",

        "remaining_amount",

        "status",

        "payment_method",

        "type",
    ];

    protected $casts = [
        "total_amount" => "float",

        "original_amount" => "float",

        "paid_amount" => "float",

        "remaining_amount" => "float",

        "period_start" => "date",

        "period_end" => "date",

        "due_date" => "date",
    ];

    public function rentalBooking()
    {
        return $this->belongsTo(RentalBooking::class);
    }

    public function payments()
    {
        return $this->hasMany(RentalPayment::class);
    }
}
