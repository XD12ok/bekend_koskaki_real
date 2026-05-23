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
        "paid_amount",
        "remaining_amount",
        "status",
        "payment_method",
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
