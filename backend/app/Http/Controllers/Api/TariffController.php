<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tariff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    /**
     * Dapatkan tarif aktif saat ini.
     */
    public function index()
    {
        $tariff = Tariff::getActiveTariff();
        return response()->json($tariff);
    }

    /**
     * Update tarif air & abodemen (khusus Super Admin).
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'rate_per_m3' => 'required|numeric|min:0',
            'abodemen' => 'required|numeric|min:0',
        ]);

        $tariff = Tariff::create([
            'rate_per_m3' => $validated['rate_per_m3'],
            'abodemen' => $validated['abodemen'],
            'effective_date' => Carbon::now()->toDateString(),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Tarif berhasil diperbarui.',
            'tariff' => $tariff,
        ]);
    }
}
