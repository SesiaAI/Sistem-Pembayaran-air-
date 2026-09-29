<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Tampilkan seluruh data pelanggan dengan filter & pencarian.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['user', 'latestMeterReading']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_no', 'like', "%{$search}%")
                  ->orWhere('meter_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($customers);
    }

    /**
     * Tambah pelanggan baru oleh Admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'nullable|string|min:6',
            'meter_number' => 'nullable|string|unique:customers,meter_number',
            'address' => 'required|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password'] ?? 'password'),
                'role' => 'pelanggan',
            ]);

            $count = Customer::count() + 1;
            $customerNo = 'PLG-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $customer = Customer::create([
                'user_id' => $user->id,
                'customer_no' => $customerNo,
                'meter_number' => $validated['meter_number'] ?? null,
                'address' => $validated['address'],
                'status' => $validated['status'] ?? 'active',
            ]);

            return response()->json([
                'message' => 'Pelanggan berhasil ditambahkan.',
                'customer' => $customer->load('user'),
            ], 201);
        });
    }

    /**
     * Detail pelanggan dengan riwayat meteran & tagihan.
     */
    public function show($id)
    {
        $customer = Customer::with([
            'user',
            'meterReadings' => function ($q) {
                $q->latest('period_year')->latest('period_month');
            },
            'bills' => function ($q) {
                $q->latest('period_year')->latest('period_month')->with('latestPayment');
            }
        ])->findOrFail($id);

        return response()->json($customer);
    }

    /**
     * Update data pelanggan.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::with('user')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $customer->user_id,
            'meter_number' => 'nullable|string|unique:customers,meter_number,' . $customer->id,
            'address' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($customer, $validated) {
            $customer->user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
            ]);

            $customer->update([
                'meter_number' => $validated['meter_number'],
                'address' => $validated['address'],
                'status' => $validated['status'],
            ]);
        });

        return response()->json([
            'message' => 'Data pelanggan berhasil diperbarui.',
            'customer' => $customer->fresh()->load('user'),
        ]);
    }

    /**
     * Hapus pelanggan.
     */
    public function destroy($id)
    {
        $customer = Customer::with('user')->findOrFail($id);

        DB::transaction(function () use ($customer) {
            $user = $customer->user;
            $customer->delete();
            if ($user) {
                $user->delete();
            }
        });

        return response()->json([
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }
}
