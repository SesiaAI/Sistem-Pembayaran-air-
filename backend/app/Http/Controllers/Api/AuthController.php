<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register Pelanggan baru (menggunakan Nomor HP & Nama).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'address' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'pelanggan',
            ]);

            // Generate nomor sambungan pelanggan otomatis
            $count = Customer::count() + 1;
            $customerNo = 'PLG-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $customer = Customer::create([
                'user_id' => $user->id,
                'customer_no' => $customerNo,
                'address' => $validated['address'] ?? 'Belum diisi',
                'status' => 'active',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registrasi pelanggan berhasil.',
                'token' => $token,
                'user' => $user->load('customer'),
            ], 201);
        });
    }

    /**
     * Login untuk semua role (Super Admin, Admin, Pelanggan) menggunakan Nomor HP.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Nomor telepon atau password salah.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $user->load('customer'),
        ]);
    }

    /**
     * Get profile user yang sedang login.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('customer'),
        ]);
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
