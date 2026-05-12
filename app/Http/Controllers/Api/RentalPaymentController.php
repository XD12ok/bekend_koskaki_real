<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\FamilyInviteCode;
use App\Models\RentalBooking;
use App\Models\RentalPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class RentalPaymentController extends Controller
{
    public function uploadProof(Request $request, $id)
    {
        $request->validate([
            "payment_proof" => "required|image|max:2048",
            "sender_name" => "nullable|string",
            "payment_method" => "nullable|string",
            "notes" => "nullable|string",
        ]);
        $booking = RentalBooking::findOrFail($id);
        $proof = $request
            ->file("payment_proof")
            ->store("rental-payments", "public");
        $payment = RentalPayment::create([
            "rental_booking_id" => $booking->id,
            "amount" => $booking->total_price,
            "payment_method" => $request->payment_method,
            "sender_name" => $request->sender_name,
            "notes" => $request->notes,
            "payment_proof" => $proof,
            "status" => "pending",
        ]);
        $booking->update([
            "status" => "waiting_confirmation",
        ]);
        return response()->json([
            "message" => "Bukti pembayaran berhasil diupload",
            "data" => $payment,
        ]);
    }
    public function approve($id)
    {
        $payment = RentalPayment::with("rentalBooking")->findOrFail($id);
        $payment->update([
            "status" => "approved",
            "verified_by" => auth()->id(),
            "verified_at" => now(),
        ]);
        $booking = $payment->rentalBooking;
        $booking->update([
            "status" => "active",
            "approved_at" => now(),
        ]);
        FamilyInviteCode::create([
            "rental_booking_id" => $booking->id,
            "place_property_id" => $booking->place_property_id,
            "code" => "FAM-" . strtoupper(Str::random(6)),
            "expired_at" => now()->addDays(7),
        ]);
        return response()->json([
            "message" => "Pembayaran berhasil diverifikasi",
        ]);
    }
    public function reject($id)
    {
        $payment = RentalPayment::findOrFail($id);
        $payment->update([
            "status" => "rejected",
        ]);
        $payment->rentalBooking->update([
            "status" => "rejected",
        ]);
        return response()->json([
            "message" => "Pembayaran ditolak",
        ]);
    }
}
