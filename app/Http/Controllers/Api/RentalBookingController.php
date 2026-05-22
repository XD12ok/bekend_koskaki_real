<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RentalBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RentalBookingController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $data = RentalBooking::with([
            "user",
            "owner",
            "property",
            "payments",
            "inviteCode",
        ])
            ->where(function ($query) use ($userId) {
                $query->where("user_id", $userId)->orWhere("owner_id", $userId);
            })
            ->latest()
            ->get();

        return response()->json([
            "data" => $data,
        ]);
    }

    public function show($id)
    {
        $userId = auth()->id();

        $data = RentalBooking::with([
            "user",
            "owner",
            "property",
            "payments",
            "inviteCode",
        ])
            ->where(function ($query) use ($userId) {
                $query->where("user_id", $userId)->orWhere("owner_id", $userId);
            })
            ->findOrFail($id);

        return response()->json([
            "data" => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            "booking_id" => "required|exists:bookings,id",
            "start_date" => "required|date",
            "duration" => "required|integer|min:1",
            "duration_type" => "required|in:week,month,year",
        ]);

        $booking = Booking::with("property")->findOrFail($request->booking_id);

        if ($booking->status !== "accepted") {
            return response()->json(
                [
                    "message" => "Booking survey belum diterima",
                ],
                422,
            );
        }

        $property = $booking->property;

        $price = 0;
        $endDate = null;

        if ($request->duration_type == "night") {
            $price = $property->price_perNight;

            $endDate = Carbon::parse($request->start_date)->addNights(
                (int) $request->duration,
            );
        }

        if ($request->duration_type == "week") {
            $price = $property->price_perWeek;

            $endDate = Carbon::parse($request->start_date)->addWeeks(
                (int) $request->duration,
            );
        }

        if ($request->duration_type == "month") {
            $price = $property->price_perMonth;

            $endDate = Carbon::parse($request->start_date)->addMonths(
                (int) $request->duration,
            );
        }

        if ($request->duration_type == "year") {
            $price = $property->price_perYear;

            $endDate = Carbon::parse($request->start_date)->addYears(
                (int) $request->duration,
            );
        }

        $totalPrice = $price * (int) $request->duration;

        $data = RentalBooking::create([
            "user_id" => auth()->id(),
            "owner_id" => $booking->owner_id,
            "booking_id" => $booking->id,

            // FIX UTAMA DI SINI
            "place_property_id" => $booking->place_properties_id,

            "start_date" => $request->start_date,
            "end_date" => $endDate,
            "duration" => $request->duration,
            "duration_type" => $request->duration_type,
            "price_per_duration" => $price,
            "total_price" => $totalPrice,
            "status" => "pending_payment",
        ]);

        return response()->json([
            "message" => "Rental booking dibuat",
            "data" => $data,
        ]);
    }

    public function cancel($id)
    {
        $data = RentalBooking::findOrFail($id);

        $data->update([
            "status" => "cancelled",
        ]);

        return response()->json([
            "message" => "Booking dibatalkan",
        ]);
    }
}
