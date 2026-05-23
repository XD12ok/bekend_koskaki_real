<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalPayment extends Model
{
    protected $fillable = [
        "rental_booking_id",
        "amount",
        "payment_method",
        "sender_name",
        "notes",
        "payment_proof",
        "status",
        "verified_by",
        "verified_at",
        "type",
    ];

    public function rentalBooking()
    {
        return $this->belongsTo(RentalBooking::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, "verified_by");
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
