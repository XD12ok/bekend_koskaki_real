<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\RentalBooking;
use App\Models\RentalPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // =========================
    // GET ALL INVOICES
    // =========================

    public function index($rentalBookingId)
    {
        $data = Invoice::with("payments")
            ->where("rental_booking_id", $rentalBookingId)
            ->latest()
            ->get();

        return response()->json([
            "data" => $data,
        ]);
    }

    // =========================
    // SHOW SINGLE INVOICE
    // =========================

    public function show($id)
    {
        $invoice = Invoice::with("payments")->findOrFail($id);

        return response()->json([
            "data" => $invoice,
        ]);
    }

    // =========================
    // CREATE MONTHLY PAYMENT
    // =========================

    public function createPayment(Request $request)
    {
        $request->validate([
            "invoice_id" => "required|exists:invoices,id",

            "claimed_amount" => "required|numeric|min:1",

            "payment_proof" => "required|image|max:2048",

            "payment_type" => "nullable|string",

            "sender_name" => "nullable|string",

            "notes" => "nullable|string",
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        // invoice sudah lunas
        if ($invoice->status === "paid") {
            return response()->json(
                [
                    "message" => "Invoice already paid",
                ],
                422,
            );
        }

        // cek invoice sebelumnya
        $hasOldInvoice = Invoice::where(
            "rental_booking_id",
            $invoice->rental_booking_id,
        )
            ->where("id", "!=", $invoice->id)
            ->whereIn("status", ["unpaid", "partial", "overdue"])
            ->where("period_start", "<", $invoice->period_start)
            ->exists();

        if ($hasOldInvoice) {
            return response()->json(
                [
                    "message" => "Please pay previous invoice first",
                ],
                422,
            );
        }

        // update payment type ke invoice
        $invoice->update([
            "payment_type" => $request->payment_type,
        ]);

        // upload proof
        $proof = $request
            ->file("payment_proof")
            ->store("invoice-payments", "public");

        // create payment
        $payment = RentalPayment::create([
            "invoice_id" => $invoice->id,

            "rental_booking_id" => $invoice->rental_booking_id,

            "claimed_amount" => $request->claimed_amount,

            "amount" => $request->claimed_amount,

            "sender_name" => $request->sender_name,

            "notes" => $request->notes,

            "payment_proof" => $proof,

            "status" => "pending",
        ]);

        return response()->json([
            "message" => "Payment uploaded successfully",

            "data" => $payment,
        ]);
    }

    // =========================
    // APPROVE PAYMENT
    // =========================

    public function approvePayment(Request $request, $id)
    {
        $request->validate([
            "verified_amount" => "required|numeric|min:1",
        ]);

        $payment = RentalPayment::with("invoice", "rentalBooking")->findOrFail(
            $id,
        );

        // sudah approved
        if ($payment->status === "approved") {
            return response()->json(
                [
                    "message" => "Payment already approved",
                ],
                422,
            );
        }

        $invoice = $payment->invoice;

        // overpayment
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

            // lunas
            if ($invoice->remaining_amount <= 0) {
                $invoice->remaining_amount = 0;

                $invoice->status = "paid";

                // update booking
                $payment->rentalBooking->update([
                    "payment_status" => "active",

                    "grace_until" => null,

                    "next_payment_date" => $invoice->period_end,
                ]);
            } else {
                // partial
                $invoice->status = "partial";

                $payment->rentalBooking->update([
                    "payment_status" => "partial",
                ]);
            }

            $invoice->save();

            DB::commit();

            return response()->json([
                "message" => "Payment approved",

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

    public function rejectPayment($id)
    {
        $payment = RentalPayment::findOrFail($id);

        $payment->update([
            "status" => "rejected",
        ]);

        return response()->json([
            "message" => "Payment rejected",
        ]);
    }

    // =========================
    // GIVE GRACE
    // =========================

    public function giveGrace(Request $request, $rentalBookingId)
    {
        $request->validate([
            "grace_until" => "required|date",
        ]);

        $booking = RentalBooking::findOrFail($rentalBookingId);

        $booking->update([
            "payment_status" => "grace",

            "grace_until" => $request->grace_until,
        ]);

        return response()->json([
            "message" => "Grace period granted",
        ]);
    }
}
