<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    /**
     * Seluruh tagihan (untuk Admin & Super Admin).
     */
    public function index(Request $request)
    {
        $query = Bill::with(['customer.user', 'meterReading', 'latestPayment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('period_month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('period_year', $request->year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('customer_no', 'like', "%{$search}%")
                         ->orWhere('meter_number', 'like', "%{$search}%")
                         ->orWhereHas('user', function ($uq) use ($search) {
                             $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $bills = $query->latest('period_year')
                       ->latest('period_month')
                       ->latest('id')
                       ->paginate($request->get('per_page', 15));

        return response()->json($bills);
    }

    /**
     * Tagihan khusus pelanggan yang sedang login.
     */
    public function myBills(Request $request)
    {
        $customer = $request->user()->customer;

        if (!$customer) {
            return response()->json([
                'message' => 'Profil pelanggan belum terhubung.',
                'data' => [],
            ], 404);
        }

        $bills = Bill::where('customer_id', $customer->id)
            ->with(['meterReading', 'latestPayment'])
            ->latest('period_year')
            ->latest('period_month')
            ->get();

        return response()->json([
            'customer' => $customer,
            'bills' => $bills,
        ]);
    }

    /**
     * Detail tagihan & struk/kuitansi digital.
     */
    public function show($id)
    {
        $bill = Bill::with([
            'customer.user',
            'meterReading.admin',
            'payments' => function ($q) {
                $q->latest();
            }
        ])->findOrFail($id);

        return response()->json($bill);
    }

    /**
     * Pembayaran Tunai / Manual di Kantor/Loket oleh Admin.
     */
    public function payCash(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);

        if ($bill->status === 'paid') {
            return response()->json([
                'message' => 'Tagihan ini sudah berstatus lunas.',
            ], 422);
        }

        DB::transaction(function () use ($bill, $request) {
            $orderId = 'CASH-' . $bill->bill_no . '-' . time();

            Payment::create([
                'bill_id' => $bill->id,
                'order_id' => $orderId,
                'payment_type' => 'cash',
                'gross_amount' => $bill->total_amount,
                'transaction_status' => 'settlement',
                'payment_method_detail' => 'Bayar Tunai di Loket (Diterima oleh ' . $request->user()->name . ')',
                'paid_at' => Carbon::now(),
            ]);

            $bill->update([
                'status' => 'paid',
            ]);
        });

        return response()->json([
            'message' => 'Pembayaran tunai berhasil dicatat. Tagihan telah lunas.',
            'bill' => $bill->fresh()->load('latestPayment'),
        ]);
    }
}
