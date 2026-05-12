<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalBooking extends Model
{
    protected $fillable = [
        "user_id",
        "owner_id",
        "booking_id",
        "place_property_id",
        "start_date",
        "end_date",
        "duration",
        "duration_type",
        "price_per_duration",
        "total_price",
        "status",
        "family_joined",
        "approved_at",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, "owner_id");
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function property()
    {
        return $this->belongsTo(PlaceProperties::class, "place_property_id");
    }

    public function payments()
    {
        return $this->hasMany(RentalPayment::class);
    }

    public function inviteCode()
    {
        return $this->hasOne(FamilyInviteCode::class);
    }
}
