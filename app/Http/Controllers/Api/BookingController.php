<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\PlaceProperties;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL BOOKINGS
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $bookings = Booking::with(["user", "property", "owner", "conversation"])
            ->latest()
            ->get();

        return response()->json([
            "data" => $bookings,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET DETAIL BOOKING
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $booking = Booking::with([
            "user",
            "property",
            "owner",
            "conversation",
            "rescheduleRequests",
        ])->findOrFail($id);

        return response()->json([
            "data" => $booking,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE BOOKING
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            "place_properties_id" => "required|exists:place_properties,id",

            "visit_date" => "required|date",

            "visit_time" => "required",
        ]);

        // ambil property
        $property = PlaceProperties::findOrFail($request->place_properties_id);

        // validasi owner
        if (!$property->owner_id) {
            return response()->json(
                [
                    "message" => "Property belum memiliki owner",
                ],
                400,
            );
        }

        // buat booking
        $booking = Booking::create([
            "user_id" => auth()->id(),

            "place_properties_id" => $property->id,

            // FIX DI SINI
            "owner_id" => $property->owner_id,

            "visit_date" => $request->visit_date,

            "visit_time" => $request->visit_time,

            "notes" => $request->notes,

            "status" => "pending",
        ]);

        // buat conversation otomatis
        Conversation::create([
            "place_property_id" => $property->id,

            "user_id" => auth()->id(),

            // FIX DI SINI
            "owner_id" => $property->owner_id,
        ]);

        return response()->json([
            "message" => "Booking berhasil dibuat",
            "data" => $booking,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCEPT BOOKING
    |--------------------------------------------------------------------------
    */
    public function accept($id)
    {
        $booking = Booking::findOrFail($id);

        $booking->update([
            "status" => "accepted",
        ]);

        return response()->json([
            "message" => "Booking diterima",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT BOOKING
    |--------------------------------------------------------------------------
    */
    public function reject($id)
    {
        $booking = Booking::findOrFail($id);

        $booking->update([
            "status" => "rejected",
        ]);

        return response()->json([
            "message" => "Booking ditolak",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL BOOKING
    |--------------------------------------------------------------------------
    */
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);

        $booking->update([
            "status" => "cancelled",
        ]);

        return response()->json([
            "message" => "Booking dibatalkan",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | COMPLETE BOOKING
    |--------------------------------------------------------------------------
    */
    public function complete($id)
    {
        $booking = Booking::findOrFail($id);

        $booking->update([
            "status" => "completed",
        ]);

        return response()->json([
            "message" => "Booking selesai",
        ]);
    }
}
