<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\RentalPayment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // =========================
    // TOTAL INCOME
    // =========================

    public function totalIncome()
    {
        $ownerId = auth()->id();

        $total = RentalPayment::where("status", "approved")
            ->whereHas("rentalBooking", function ($query) use ($ownerId) {
                $query->where("owner_id", $ownerId);
            })
            ->sum("verified_amount");

        return response()->json([
            "total_income" => $total,
        ]);
    }

    // =========================
    // INCOME REPORT
    // =========================

    public function incomeReport(Request $request)
    {
        $ownerId = auth()->id();

        $query = RentalPayment::with(["rentalBooking", "invoice"])
            ->where("status", "approved")
            ->whereHas("rentalBooking", function ($query) use ($ownerId) {
                $query->where("owner_id", $ownerId);
            });

        // filter tanggal
        if ($request->filled("start_date")) {
            $query->whereDate("verified_at", ">=", $request->start_date);
        }

        if ($request->filled("end_date")) {
            $query->whereDate("verified_at", "<=", $request->end_date);
        }

        $payments = $query->latest()->get();

        $total = $payments->sum("verified_amount");

        return response()->json([
            "total_income" => $total,
            "total_transactions" => $payments->count(),
            "data" => $payments,
        ]);
    }

    // =========================
    // OVERDUE REPORT
    // =========================

    public function overdueReport()
    {
        $ownerId = auth()->id();

        $invoices = Invoice::with(["rentalBooking", "payments"])
            ->whereIn("status", ["unpaid", "partial", "overdue"])
            ->whereDate("due_date", "<", now())
            ->whereHas("rentalBooking", function ($query) use ($ownerId) {
                $query->where("owner_id", $ownerId);
            })
            ->latest()
            ->get();

        $totalOverdue = $invoices->sum("remaining_amount");

        return response()->json([
            "total_overdue" => $totalOverdue,

            "total_invoices" => $invoices->count(),

            "data" => $invoices,
        ]);
    }
}
