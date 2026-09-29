<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Tariff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MeterReadingController extends Controller
{
    /**
     * Tampilkan riwayat pencatatan meteran.
     */
    public function index(Request $request)
    {
        $query = MeterReading::with(['customer.user', 'admin', 'bill']);

        if ($request->filled('month')) {
            $query->where('period_month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('period_year', $request->year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('customer_no', 'like', "%{$search}%")
                  ->orWhere('meter_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $readings = $query->latest('period_year')
                          ->latest('period_month')
                          ->latest('id')
                          ->paginate($request->get('per_page', 15));

        return response()->json($readings);
    }

    /**
     * Ambil stand meter terakhir pelanggan (untuk autofill di form catat meter).
     */
    public function getLatestMeter($customerId)
    {
        $customer = Customer::with('user')->findOrFail($customerId);

        $latestReading = MeterReading::where('customer_id', $customerId)
            ->latest('period_year')
            ->latest('period_month')
            ->first();

        $initialMeter = $latestReading ? $latestReading->final_meter : 0;

        return response()->json([
            'customer' => $customer,
            'initial_meter' => $initialMeter,
            'last_period' => $latestReading ? "{$latestReading->period_month}/{$latestReading->period_year}" : null,
        ]);
    }

    /**
     * Admin input angka stand meter -> Sistem otomatis buat Tagihan (Bill).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030',
            'final_meter' => 'required|integer|min:0',
            'initial_meter' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:5120', // max 5MB foto
        ]);

        // Cek apakah periode ini sudah pernah dicatat
        $exists = MeterReading::where('customer_id', $validated['customer_id'])
            ->where('period_month', $validated['period_month'])
            ->where('period_year', $validated['period_year'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'period_month' => ['Meteran untuk pelanggan dan periode ini sudah pernah dicatat.'],
            ]);
        }

        // Tentukan stand meter awal
        $latestReading = MeterReading::where('customer_id', $validated['customer_id'])
            ->latest('period_year')
            ->latest('period_month')
            ->first();

        $initialMeter = $validated['initial_meter'] ?? ($latestReading ? $latestReading->final_meter : 0);

        if ($validated['final_meter'] < $initialMeter) {
            throw ValidationException::withMessages([
                'final_meter' => ["Stand meter akhir ({$validated['final_meter']}) tidak boleh lebih kecil dari stand awal ({$initialMeter})."],
            ]);
        }

        $totalUsage = $validated['final_meter'] - $initialMeter;

        // Upload foto jika ada
        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('meter_photos', 'public');
            $photoUrl = '/storage/' . $path;
        }

        return DB::transaction(function () use ($request, $validated, $initialMeter, $totalUsage, $photoUrl) {
            // 1. Simpan pencatatan meter
            $reading = MeterReading::create([
                'customer_id' => $validated['customer_id'],
                'admin_id' => $request->user()->id,
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'initial_meter' => $initialMeter,
                'final_meter' => $validated['final_meter'],
                'total_usage' => $totalUsage,
                'photo_url' => $photoUrl,
                'reading_date' => Carbon::now()->toDateString(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // 2. Ambil tarif aktif (Default: rate 5.000, abodemen 5.000)
            $tariff = Tariff::getActiveTariff();
            $ratePerM3 = $tariff->rate_per_m3 ?? 5000.00;
            $abodemen = $tariff->abodemen ?? 5000.00;

            $usageCost = $totalUsage * $ratePerM3;
            $totalAmount = $usageCost + $abodemen;

            // Jatuh tempo tanggal 20 bulan berikutnya
            $dueDate = Carbon::create($validated['period_year'], $validated['period_month'], 1)
                ->addMonth()
                ->setDay(20)
                ->toDateString();

            // Generate nomor tagihan unik
            $billCount = Bill::count() + 1;
            $billNo = sprintf('TAG-%04d%02d-%04d', $validated['period_year'], $validated['period_month'], $billCount);

            // 3. Terbitkan Tagihan (Bill)
            $bill = Bill::create([
                'bill_no' => $billNo,
                'customer_id' => $validated['customer_id'],
                'meter_reading_id' => $reading->id,
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'rate_per_m3' => $ratePerM3,
                'total_usage' => $totalUsage,
                'usage_cost' => $usageCost,
                'abodemen_cost' => $abodemen,
                'total_amount' => $totalAmount,
                'due_date' => $dueDate,
                'status' => 'unpaid',
            ]);

            return response()->json([
                'message' => 'Pencatatan meter berhasil dan tagihan telah otomatis diterbitkan.',
                'meter_reading' => $reading->load('customer.user'),
                'bill' => $bill,
            ], 201);
        });
    }

    /**
     * Hapus pencatatan meter (dan tagihannya).
     */
    public function destroy($id)
    {
        $reading = MeterReading::with('bill.payments')->findOrFail($id);

        if ($reading->bill && $reading->bill->status === 'paid') {
            return response()->json([
                'message' => 'Tidak dapat menghapus pencatatan yang tagihannya sudah lunas.',
            ], 422);
        }

        $reading->delete();

        return response()->json([
            'message' => 'Pencatatan meter dan tagihan berhasil dihapus.',
        ]);
    }
}
