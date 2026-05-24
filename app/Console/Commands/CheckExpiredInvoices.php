<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiredInvoices extends Command
{
    protected $signature = "invoices:check-expired";

    protected $description = "Check expired invoices and apply penalties";

    public function handle()
    {
        $today = Carbon::today();

        $invoices = Invoice::with("rentalBooking")
            ->where("status", "!=", "paid")
            ->get();

        foreach ($invoices as $invoice) {
            // =========================
            // SAVE ORIGINAL AMOUNT
            // =========================

            if (!$invoice->original_amount) {
                $invoice->original_amount = $invoice->total_amount;

                $invoice->save();
            }

            $booking = $invoice->rentalBooking;

            // =========================
            // GET LAST PENALTY DATE
            // =========================

            $lastPenaltyDate = $invoice->last_penalty_at
                ? Carbon::parse($invoice->last_penalty_at)
                : Carbon::parse($invoice->period_end);

            // =========================
            // PENALTY ONLY EVERY MONTH
            // =========================

            $canApplyPenalty = $today->greaterThanOrEqualTo(
                $lastPenaltyDate->copy()->addMonth(),
            );

            // =========================
            // STATUS GRACE
            // =========================

            if (
                $invoice->status === "grace" &&
                $booking &&
                $booking->grace_until &&
                $canApplyPenalty
            ) {
                $newTotal = $invoice->total_amount * 2;

                $invoice->update([
                    "total_amount" => $newTotal,

                    "paid_amount" => 0,

                    "remaining_amount" => $newTotal,

                    "last_penalty_at" => now(),
                ]);

                $booking->update([
                    "payment_status" => "unpaid",
                ]);
            }

            // =========================
            // PARTIAL EXPIRED
            // =========================
            elseif (
                $invoice->status === "partial" &&
                $invoice->remaining_amount > 0 &&
                Carbon::parse($invoice->period_end)->lt($today) &&
                $canApplyPenalty
            ) {
                $newTotal = $invoice->total_amount + $invoice->remaining_amount;

                $invoice->update([
                    "status" => "unpaid",

                    "total_amount" => $newTotal,

                    "paid_amount" => 0,

                    "remaining_amount" => $newTotal,

                    "last_penalty_at" => now(),
                ]);

                if ($booking) {
                    $booking->update([
                        "payment_status" => "unpaid",
                    ]);
                }
            }

            // =========================
            // UNPAID / OVERDUE
            // =========================
            elseif (
                in_array($invoice->status, ["unpaid", "overdue"]) &&
                Carbon::parse($invoice->period_end)->lt($today)
            ) {
                $invoice->update([
                    "paid_amount" => 0,

                    "remaining_amount" => $invoice->total_amount,
                ]);

                if ($booking) {
                    $booking->update([
                        "payment_status" => "unpaid",
                    ]);
                }
            }
        }

        $this->info("Expired invoices checked successfully.");
    }
}
