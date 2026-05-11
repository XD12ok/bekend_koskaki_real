<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRescheduleRequest;
use Illuminate\Http\Request;

class BookingRescheduleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            "booking_id" => "required|exists:bookings,id",
            "new_visit_date" => "required|date",
            "new_visit_time" => "required",
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        $reschedule = BookingRescheduleRequest::create([
            "booking_id" => $booking->id,
            "requested_by" => auth()->id(),

            "old_visit_date" => $booking->visit_date,
            "old_visit_time" => $booking->visit_time,

            "new_visit_date" => $request->new_visit_date,
            "new_visit_time" => $request->new_visit_time,

            "reason" => $request->reason,

            "approved_by_user" => auth()->id() == $booking->user_id,

            "approved_by_owner" => auth()->id() == $booking->owner_id,
        ]);

        return response()->json([
            "message" => "Permintaan reschedule dibuat",
            "data" => $reschedule,
        ]);
    }

    public function approve($id)
    {
        $reschedule = BookingRescheduleRequest::findOrFail($id);

        $booking = $reschedule->booking;

        if (auth()->id() == $booking->user_id) {
            $reschedule->approved_by_user = true;
        }

        if (auth()->id() == $booking->owner_id) {
            $reschedule->approved_by_owner = true;
        }

        if ($reschedule->approved_by_user && $reschedule->approved_by_owner) {
            $booking->update([
                "visit_date" => $reschedule->new_visit_date,

                "visit_time" => $reschedule->new_visit_time,
            ]);

            $reschedule->status = "accepted";
        }

        $reschedule->save();

        return response()->json([
            "message" => "Reschedule disetujui",
        ]);
    }

    public function reject($id)
    {
        $reschedule = BookingRescheduleRequest::findOrFail($id);

        $reschedule->update([
            "status" => "rejected",
        ]);

        return response()->json([
            "message" => "Permintaan reschedule ditolak",
        ]);
    }
}
