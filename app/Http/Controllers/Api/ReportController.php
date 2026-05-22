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
        $total = RentalPayment::where("status", "approved")->sum(
            "verified_amount",
        );

        return response()->json([
            "total_income" => $total,
        ]);
    }

    // =========================
    // INCOME REPORT
    // =========================

    public function incomeReport(Request $request)
    {
        $query = RentalPayment::with(["rentalBooking", "invoice"])->where(
            "status",
            "approved",
        );

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
        $invoices = Invoice::with(["rentalBooking", "payments"])
            ->whereIn("status", ["unpaid", "partial", "overdue"])
            ->whereDate("due_date", "<", now())
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
