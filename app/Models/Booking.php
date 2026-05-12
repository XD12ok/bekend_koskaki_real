<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        "user_id",
        "place_properties_id",
        "owner_id",
        "visit_date",
        "visit_time",
        "status",
        "notes",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(PlaceProperties::class, "place_properties_id");
    }
    public function owner()
    {
        return $this->belongsTo(User::class, "owner_id");
    }

    public function rescheduleRequests()
    {
        return $this->hasMany(BookingRescheduleRequest::class);
    }

    public function conversation()
    {
        return $this->hasOne(
            Conversation::class,
            "place_property_id",
            "place_properties_id",
        );
    }
}
