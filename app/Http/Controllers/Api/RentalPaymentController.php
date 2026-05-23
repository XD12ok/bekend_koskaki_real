<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyInviteCode;
use App\Models\Invoice;
use App\Models\RentalBooking;
use App\Models\RentalPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RentalPaymentController extends Controller
{
    // =========================
    // UPLOAD INITIAL PAYMENT
    // =========================

    public function uploadProof(Request $request, $id)
    {
        $request->validate([
            "payment_proof" => "required|image|max:2048",

            // sekarang tenant bisa partial
            "claimed_amount" => "required|numeric|min:1",

            "sender_name" => "nullable|string",

            "payment_method" => "nullable|string",

            "notes" => "nullable|string",
        ]);

        $booking = RentalBooking::findOrFail($id);

        // ambil invoice
        $invoice = Invoice::where(
            "rental_booking_id",
            $booking->id,
        )->firstOrFail();

        // invoice sudah lunas
        if ($invoice->status === "paid") {
            return response()->json(
                [
                    "message" => "Invoice already paid",
                ],
                422,
            );
        }

        // claimed tidak boleh melebihi sisa
        if ($request->claimed_amount > $invoice->remaining_amount) {
            return response()->json(
                [
                    "message" => "Claimed amount exceeds remaining invoice",
                ],
                422,
            );
        }

        // upload proof
        $proof = $request
            ->file("payment_proof")
            ->store("rental-payments", "public");

        // create payment
        $payment = RentalPayment::create([
            "invoice_id" => $invoice->id,

            "rental_booking_id" => $booking->id,

            "claimed_amount" => $request->claimed_amount,

            "amount" => $request->claimed_amount,

            "type" => "initial",

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

    // =========================
    // APPROVE INITIAL PAYMENT
    // =========================

    public function approve(Request $request, $id)
    {
        $request->validate([
            "verified_amount" => "required|numeric|min:1",
        ]);

        $payment = RentalPayment::with([
            "rentalBooking",
            "invoice",
        ])->findOrFail($id);

        // sudah approved
        if ($payment->status === "approved") {
            return response()->json(
                [
                    "message" => "Payment already approved",
                ],
                422,
            );
        }

        $booking = $payment->rentalBooking;

        $invoice = $payment->invoice;

        // overpayment protection
        if ($request->verified_amount > $invoice->remaining_amount) {
            return response()->json(
                [
                    "message" => "Verified amount exceeds remaining invoice",
                ],
                422,
            );
        }

        DB::beginTransaction();

        try {
            // =====================
            // UPDATE PAYMENT
            // =====================

            $payment->update([
                "verified_amount" => $request->verified_amount,

                "amount" => $request->verified_amount,

                "status" => "approved",

                "verified_by" => auth()->id(),

                "verified_at" => now(),
            ]);

            // =====================
            // UPDATE INVOICE
            // =====================

            $invoice->paid_amount += $request->verified_amount;

            $invoice->remaining_amount =
                $invoice->total_amount - $invoice->paid_amount;

            // invoice lunas
            if ($invoice->remaining_amount <= 0) {
                $invoice->remaining_amount = 0;

                $invoice->status = "paid";
            } else {
                $invoice->status = "partial";
            }

            $invoice->save();

            // =====================
            // AKTIFKAN BOOKING
            // JIKA LUNAS
            // =====================

            if ($invoice->status === "paid") {
                $booking->update([
                    "status" => "active",

                    "approved_at" => now(),
                ]);

                // cegah duplicate invite
                $hasInvite = FamilyInviteCode::where(
                    "rental_booking_id",
                    $booking->id,
                )->exists();

                if (!$hasInvite) {
                    FamilyInviteCode::create([
                        "rental_booking_id" => $booking->id,

                        "place_property_id" => $booking->place_property_id,

                        "code" => "FAM-" . strtoupper(Str::random(6)),

                        "expired_at" => now()->addDays(7),
                    ]);
                }
            } else {
                // masih partial
                $booking->update([
                    "status" => "partial_payment",
                ]);
            }

            DB::commit();

            return response()->json([
                "message" => "Pembayaran berhasil diverifikasi",

                "invoice" => $invoice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                [
                    "message" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    // =========================
    // REJECT PAYMENT
    // =========================

    public function reject($id)
    {
        $payment = RentalPayment::findOrFail($id);

        // sudah reject
        if ($payment->status === "rejected") {
            return response()->json(
                [
                    "message" => "Payment already rejected",
                ],
                422,
            );
        }

        $payment->update([
            "status" => "rejected",
        ]);

        // tenant bisa upload ulang
        $payment->rentalBooking->update([
            "status" => "pending_payment",
        ]);

        return response()->json([
            "message" => "Pembayaran ditolak",
        ]);
    }
}
