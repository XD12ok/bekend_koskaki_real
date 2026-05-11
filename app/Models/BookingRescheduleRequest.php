<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRescheduleRequest extends Model
{
    protected $fillable = [
        "booking_id",
        "requested_by",
        "old_visit_date",
        "old_visit_time",
        "new_visit_date",
        "new_visit_time",
        "reason",
        "approved_by_user",
        "approved_by_owner",
        "status",
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, "requested_by");
    }
}
