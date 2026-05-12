<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyInviteCode extends Model
{
    protected $fillable = [
        "rental_booking_id",
        "place_property_id",
        "code",
        "expired_at",
        "used_at",
        "is_used",
    ];

    protected $casts = [
        "expired_at" => "datetime",
        "used_at" => "datetime",
    ];

    public function rentalBooking()
    {
        return $this->belongsTo(RentalBooking::class);
    }

    public function property()
    {
        return $this->belongsTo(PlaceProperties::class, "place_property_id");
    }
}
