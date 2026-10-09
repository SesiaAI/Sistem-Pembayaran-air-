<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MeterReadingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TariffController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sistem Pembayaran Air
|--------------------------------------------------------------------------
*/

// --- Public Routes ---
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Midtrans Webhook (Public Callback)
Route::post('/payment/webhook', [PaymentController::class, 'webhook']);
Route::get('/tariffs/active', [TariffController::class, 'index']);

// --- Protected Routes (Harus Login) ---
Route::middleware('auth:sanctum')->group(function () {
    // Auth info & logout
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Tagihan & Pembayaran (Bisa diakses Pelanggan & Admin)
    Route::get('/bills/{id}', [BillController::class, 'show']);
    Route::post('/bills/{id}/snap-token', [PaymentController::class, 'createSnapToken']);
    Route::post('/bills/{id}/sync-payment', [PaymentController::class, 'syncPayment']);
    Route::post('/bills/{id}/simulate-payment', [PaymentController::class, 'simulateSuccess']);

    // Area Pelanggan
    Route::prefix('pelanggan')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'pelangganDashboard']);
        Route::get('/bills', [BillController::class, 'myBills']);
    });

    // Area Admin & Super Admin
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('customers', CustomerController::class);
    Route::get('/meter-readings/latest/{customerId}', [MeterReadingController::class, 'getLatestMeter']);
    Route::apiResource('meter-readings', MeterReadingController::class)->only(['index', 'store', 'destroy']);
    Route::get('/bills', [BillController::class, 'index']);
    Route::post('/bills/{id}/pay-cash', [BillController::class, 'payCash']);

    // Area Super Admin
    Route::post('/tariffs', [TariffController::class, 'update']);
    Route::apiResource('users', UserController::class);
});
