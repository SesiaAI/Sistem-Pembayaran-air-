<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard statistik untuk Super Admin & Admin.
     */
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalCustomers = Customer::where('status', 'active')->count();

        // Tagihan bulan ini
        $totalBillsThisMonth = Bill::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->count();

        // Pendapatan bulan ini (dari tagihan status paid)
        $revenueThisMonth = Bill::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->where('status', 'paid')
            ->sum('total_amount');

        // Total tunggakan (seluruh tagihan unpaid)
        $unpaidBillsCount = Bill::where('status', 'unpaid')->count();
        $unpaidTotalAmount = Bill::where('status', 'unpaid')->sum('total_amount');

        // Total air terpakai bulan ini (m3)
        $waterUsageThisMonth = MeterReading::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->sum('total_usage');

        // Data grafik 6 bulan terakhir
        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $m = $date->month;
            $y = $date->year;
            $monthName = $date->translatedFormat('M Y');

            $revenue = Bill::where('period_month', $m)
                ->where('period_year', $y)
                ->where('status', 'paid')
                ->sum('total_amount');

            $usage = MeterReading::where('period_month', $m)
                ->where('period_year', $y)
                ->sum('total_usage');

            $monthlyChart[] = [
                'period' => $monthName,
                'month' => $m,
                'year' => $y,
                'revenue' => (float) $revenue,
                'usage' => (int) $usage,
            ];
        }

        // Transaksi pembayaran terbaru
        $recentPayments = Payment::with(['bill.customer.user'])
            ->where('transaction_status', 'settlement')
            ->latest('paid_at')
            ->limit(5)
            ->get();

        return response()->json([
            'total_customers' => $totalCustomers,
            'total_bills_this_month' => $totalBillsThisMonth,
            'revenue_this_month' => (float) $revenueThisMonth,
            'unpaid_bills_count' => $unpaidBillsCount,
            'unpaid_total_amount' => (float) $unpaidTotalAmount,
            'water_usage_this_month' => (int) $waterUsageThisMonth,
            'monthly_chart' => $monthlyChart,
            'recent_payments' => $recentPayments,
        ]);
    }

    /**
     * Dashboard statistik untuk Pelanggan.
     */
    public function pelangganDashboard(Request $request)
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            return response()->json(['message' => 'Customer profile not found'], 404);
        }

        // Tagihan aktif (belum dibayar)
        $activeBill = Bill::where('customer_id', $customer->id)
            ->where('status', 'unpaid')
            ->latest('period_year')
            ->latest('period_month')
            ->with('meterReading')
            ->first();

        $totalPaidBills = Bill::where('customer_id', $customer->id)
            ->where('status', 'paid')
            ->count();

        // Riwayat pemakaian air 6 bulan terakhir
        $historyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $m = $date->month;
            $y = $date->year;

            $reading = MeterReading::where('customer_id', $customer->id)
                ->where('period_month', $m)
                ->where('period_year', $y)
                ->first();

            $historyChart[] = [
                'period' => $date->translatedFormat('M Y'),
                'usage' => $reading ? $reading->total_usage : 0,
            ];
        }

        return response()->json([
            'customer' => $customer,
            'active_bill' => $activeBill,
            'total_paid_bills' => $totalPaidBills,
            'history_chart' => $historyChart,
        ]);
    }
}
