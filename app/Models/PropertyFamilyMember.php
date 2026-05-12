<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyFamilyMember extends Model
{
    protected $fillable = [
        "place_property_id",
        "user_id",
        "rental_booking_id",
        "joined_at",
    ];

    public function property()
    {
        return $this->belongsTo(PlaceProperties::class, "place_property_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rentalBooking()
    {
        return $this->belongsTo(RentalBooking::class);
    }
}
