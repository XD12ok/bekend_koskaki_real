<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyInviteCode;
use App\Models\Invoice;
use App\Models\RentalBooking;
use App\Models\RentalPayment;
use Carbon\Carbon;
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
            "claimed_amount" => "required|numeric|min:1",
            "sender_name" => "nullable|string",
            "payment_method" => "nullable|string",
            "notes" => "nullable|string",
        ]);

        $booking = RentalBooking::findOrFail($id);

        // =========================
        // CARI / BUAT INVOICE
        // =========================

        $invoice = Invoice::firstOrCreate(
            [
                "rental_booking_id" => $booking->id,
            ],
            [
                "period_start" => $booking->start_date,

                "period_end" => $booking->end_date,

                "due_date" => now()->addDays(3),

                "total_amount" => $booking->total_price ?? 0,

                "original_amount" => $booking->total_price ?? 0,

                "paid_amount" => 0,

                "remaining_amount" => $booking->total_price ?? 0,

                "status" => "unpaid",

                "billing_cycle" => $booking->duration_type,
            ],
        );

        // =========================
        // CEK SUDAH LUNAS
        // =========================

        if ($invoice->status === "paid") {
            return response()->json(
                [
                    "message" => "Invoice already paid",
                ],
                422,
            );
        }

        // =========================
        // CEK OVERPAYMENT
        // =========================

        if ($request->claimed_amount > $invoice->remaining_amount) {
            return response()->json(
                [
                    "message" => "Claimed amount exceeds remaining invoice",
                ],
                422,
            );
        }

        // =========================
        // UPLOAD BUKTI
        // =========================

        $proof = $request
            ->file("payment_proof")
            ->store("rental-payments", "public");

        // =========================
        // CREATE PAYMENT
        // =========================

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

        // =========================
        // UPDATE BOOKING
        // =========================

        $booking->update([
            "status" => "waiting_confirmation",
        ]);

        return response()->json([
            "message" => "Bukti pembayaran berhasil diupload",

            "payment" => $payment,

            "invoice" => $invoice,
        ]);
    }

    // =========================
    // APPROVE PAYMENT
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

        // =========================
        // SUDAH APPROVED
        // =========================

        if ($payment->status === "approved") {
            return response()->json(
                [
                    "message" => "Payment already approved",
                ],
                422,
            );
        }

        $booking = $payment->rentalBooking;

        // =========================
        // CEK / BUAT INVOICE
        // =========================

        $invoice = $payment->invoice;

        if (!$invoice) {
            $invoice = Invoice::where(
                "rental_booking_id",
                $booking->id,
            )->first();

            if (!$invoice) {
                $invoice = Invoice::create([
                    "rental_booking_id" => $booking->id,

                    "period_start" => $booking->start_date,

                    "period_end" => $booking->end_date,

                    "due_date" => now()->addDays(3),

                    "total_amount" => $booking->total_price ?? 0,

                    "original_amount" => $booking->total_price ?? 0,

                    "paid_amount" => 0,

                    "remaining_amount" => $booking->total_price ?? 0,

                    "status" => "unpaid",

                    "billing_cycle" => $booking->duration_type,
                ]);
            }

            // sambungkan payment ke invoice
            $payment->update([
                "invoice_id" => $invoice->id,
            ]);
        }

        // =========================
        // CEK OVERPAYMENT
        // =========================

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
            // =========================
            // UPDATE PAYMENT
            // =========================

            $payment->update([
                "verified_amount" => $request->verified_amount,

                "amount" => $request->verified_amount,

                "status" => "approved",

                "verified_by" => auth()->id(),

                "verified_at" => now(),
            ]);

            // =========================
            // UPDATE INVOICE
            // =========================

            $invoice->paid_amount += $request->verified_amount;

            $invoice->remaining_amount =
                $invoice->total_amount - $invoice->paid_amount;

            // =========================
            // STATUS INVOICE
            // =========================

            if ($invoice->remaining_amount <= 0) {
                $invoice->remaining_amount = 0;

                $invoice->status = "paid";

                // reset penalty
                $invoice->last_penalty_at = null;

                // reset harga asli
                if ($invoice->original_amount) {
                    $invoice->total_amount = $invoice->original_amount;
                }

                // =========================
                // PERPANJANG MASA SEWA
                // =========================

                $currentEndDate = Carbon::parse($booking->end_date);

                $duration = $booking->duration ?? 1;

                switch ($booking->duration_type) {
                    case "daily":
                        $newEndDate = $currentEndDate
                            ->copy()
                            ->addDays($duration);

                        break;

                    case "weekly":
                        $newEndDate = $currentEndDate
                            ->copy()
                            ->addWeeks($duration);

                        break;

                    case "monthly":
                        $newEndDate = $currentEndDate
                            ->copy()
                            ->addMonths($duration);

                        break;

                    case "yearly":
                        $newEndDate = $currentEndDate
                            ->copy()
                            ->addYears($duration);

                        break;

                    default:
                        $newEndDate = $currentEndDate->copy()->addMonth();

                        break;
                }

                // update booking
                $booking->update([
                    "status" => "active",

                    "approved_at" => now(),

                    "end_date" => $newEndDate,

                    "next_payment_date" => $newEndDate,
                ]);

                // update invoice
                $invoice->period_end = $newEndDate;

                // =========================
                // CREATE FAMILY INVITE
                // =========================

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
                // partial
                $invoice->status = "partial";

                $booking->update([
                    "status" => "waiting_confirmation",
                ]);
            }

            $invoice->save();

            DB::commit();

            return response()->json([
                "message" => "Pembayaran berhasil diverifikasi",

                "payment" => $payment,

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
