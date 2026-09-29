<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }

    /**
     * Generate Midtrans Snap Token untuk pembayaran online tagihan.
     */
    public function createSnapToken(Request $request, $billId)
    {
        $bill = Bill::with(['customer.user'])->findOrFail($billId);

        if ($bill->status === 'paid') {
            return response()->json([
                'message' => 'Tagihan ini sudah lunas.',
            ], 422);
        }

        $user = $bill->customer->user;
        $orderId = 'ORD-' . $bill->bill_no . '-' . time();
        $grossAmount = (int) round($bill->total_amount);

        // Parameter transaksi Midtrans Snap
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email ?? ($user->phone . '@air.test'),
            ],
            'item_details' => [
                [
                    'id' => 'WATER-USAGE',
                    'price' => (int) round($bill->usage_cost),
                    'quantity' => 1,
                    'name' => "Pemakaian Air ({$bill->total_usage} m3)",
                ],
                [
                    'id' => 'ABODEMEN',
                    'price' => (int) round($bill->abodemen_cost),
                    'quantity' => 1,
                    'name' => 'Biaya Abodemen Bulanan',
                ],
            ],
        ];

        $snapToken = null;
        $isMock = false;

        // Coba panggil Midtrans Snap SDK jika server key sudah diatur
        $serverKey = config('midtrans.server_key');
        if ($serverKey && !str_contains($serverKey, 'YOUR_SERVER_KEY')) {
            try {
                $snapToken = Snap::getSnapToken($params);
            } catch (Exception $e) {
                Log::warning('Midtrans Snap generation failed: ' . $e->getMessage());
                // Fallback ke mock token jika Midtrans server menolak (misal key salah/expired)
                $snapToken = 'DEV-MOCK-SNAP-' . bin2hex(random_bytes(16));
                $isMock = true;
            }
        } else {
            // Mode Dev Mock Token sebelum user memasukkan Server Key asli
            $snapToken = 'DEV-MOCK-SNAP-' . bin2hex(random_bytes(16));
            $isMock = true;
        }

        // Catat record payment
        $payment = Payment::create([
            'bill_id' => $bill->id,
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'pending',
            'snap_token' => $snapToken,
            'payment_type' => 'midtrans',
            'payment_method_detail' => 'Midtrans Payment Gateway (QRIS/VA)',
        ]);

        return response()->json([
            'snap_token' => $snapToken,
            'client_key' => config('midtrans.client_key'),
            'order_id' => $orderId,
            'bill' => $bill,
            'is_mock' => $isMock,
            'message' => $isMock ? 'Mode Simulasi Dev (Midtrans key belum dipasang, tombol simulasi bayar siap digunakan).' : 'Snap Token berhasil dibuat.',
        ]);
    }

    /**
     * Fitur Simulasi Pembayaran Berhasil (sangat berguna untuk testing demo tanpa uang asli).
     */
    public function simulateSuccess(Request $request, $billId)
    {
        $bill = Bill::findOrFail($billId);

        if ($bill->status === 'paid') {
            return response()->json([
                'message' => 'Tagihan sudah berstatus lunas.',
            ], 422);
        }

        DB::transaction(function () use ($bill, $request) {
            $payment = Payment::where('bill_id', $bill->id)
                ->where('transaction_status', 'pending')
                ->latest()
                ->first();

            $paymentMethod = $request->get('payment_type', 'qris_simulated');

            if ($payment) {
                $payment->update([
                    'transaction_status' => 'settlement',
                    'payment_type' => $paymentMethod,
                    'paid_at' => Carbon::now(),
                    'payment_method_detail' => 'Simulasi Pembayaran Midtrans QRIS Sukses',
                ]);
            } else {
                $payment = Payment::create([
                    'bill_id' => $bill->id,
                    'order_id' => 'SIM-' . $bill->bill_no . '-' . time(),
                    'gross_amount' => $bill->total_amount,
                    'transaction_status' => 'settlement',
                    'payment_type' => $paymentMethod,
                    'payment_method_detail' => 'Simulasi Pembayaran Berhasil',
                    'paid_at' => Carbon::now(),
                ]);
            }

            $bill->update([
                'status' => 'paid',
            ]);
        });

        return response()->json([
            'message' => 'Simulasi pembayaran berhasil! Status tagihan otomatis menjadi LUNAS.',
            'bill' => $bill->fresh()->load('latestPayment'),
        ]);
    }

    /**
     * Webhook Notification Handler resmi dari Midtrans.
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Received: ', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Verifikasi Signature Key Midtrans
        $serverKey = config('midtrans.server_key');
        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $validSignature) {
            Log::warning('Midtrans Webhook Signature Mismatch: ' . $orderId);
            return response()->json(['message' => 'Signature mismatch'], 403);
        }

        $payment = Payment::where('order_id', $orderId)->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $bill = $payment->bill;
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        DB::transaction(function () use ($payment, $bill, $payload, $transactionStatus, $fraudStatus) {
            $payment->update([
                'midtrans_response' => $payload,
                'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
            ]);

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $payment->update(['transaction_status' => 'settlement', 'paid_at' => Carbon::now()]);
                    $bill->update(['status' => 'paid']);
                }
            } else if ($transactionStatus == 'settlement') {
                $payment->update(['transaction_status' => 'settlement', 'paid_at' => Carbon::now()]);
                $bill->update(['status' => 'paid']);
            } else if ($transactionStatus == 'pending') {
                $payment->update(['transaction_status' => 'pending']);
            } else if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $payment->update(['transaction_status' => $transactionStatus]);
            }
        });

        return response()->json(['message' => 'Webhook processed successfully']);
    }
}
