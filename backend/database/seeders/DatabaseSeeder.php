<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Tariff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial Tariff (Rp 5.000/m3, Abodemen Rp 5.000)
        Tariff::create([
            'rate_per_m3' => 5000.00,
            'abodemen' => 5000.00,
            'effective_date' => Carbon::now()->startOfYear(),
            'is_active' => true,
        ]);

        // 2. Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'phone' => '081234567890',
            'email' => 'superadmin@air.test',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // 3. Admin Lapangan
        $admin = User::create([
            'name' => 'Admin Lapangan',
            'phone' => '081234567891',
            'email' => 'admin@air.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 4. Sample Pelanggan 1
        $user1 = User::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567892',
            'email' => 'budi@air.test',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
        ]);
        $cust1 = Customer::create([
            'user_id' => $user1->id,
            'customer_no' => 'PLG-2026-0001',
            'meter_number' => 'MTR-10021',
            'address' => 'Jl. Melati No. 12, RT 01/RW 02',
            'status' => 'active',
        ]);

        // Sample Pelanggan 2
        $user2 = User::create([
            'name' => 'Siti Rahayu',
            'phone' => '081234567893',
            'email' => 'siti@air.test',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
        ]);
        $cust2 = Customer::create([
            'user_id' => $user2->id,
            'customer_no' => 'PLG-2026-0002',
            'meter_number' => 'MTR-10022',
            'address' => 'Jl. Mawar No. 05, RT 02/RW 02',
            'status' => 'active',
        ]);

        // Sample Pelanggan 3
        $user3 = User::create([
            'name' => 'Ahmad Fauzi',
            'phone' => '081234567894',
            'email' => 'ahmad@air.test',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
        ]);
        $cust3 = Customer::create([
            'user_id' => $user3->id,
            'customer_no' => 'PLG-2026-0003',
            'meter_number' => 'MTR-10023',
            'address' => 'Jl. Anggrek No. 08, RT 03/RW 02',
            'status' => 'active',
        ]);

        // 5. Sample Meter Readings & Bills
        // Budi Santoso - Bulan 8 (Agustus 2026) -> LUNAS
        $reading1 = MeterReading::create([
            'customer_id' => $cust1->id,
            'admin_id' => $admin->id,
            'period_month' => 8,
            'period_year' => 2026,
            'initial_meter' => 0,
            'final_meter' => 18,
            'total_usage' => 18,
            'reading_date' => Carbon::create(2026, 8, 25),
        ]);
        Bill::create([
            'bill_no' => 'TAG-202608-0001',
            'customer_id' => $cust1->id,
            'meter_reading_id' => $reading1->id,
            'period_month' => 8,
            'period_year' => 2026,
            'rate_per_m3' => 5000.00,
            'total_usage' => 18,
            'usage_cost' => 18 * 5000.00,
            'abodemen_cost' => 5000.00,
            'total_amount' => (18 * 5000.00) + 5000.00,
            'due_date' => Carbon::create(2026, 9, 20),
            'status' => 'paid',
        ]);

        // Budi Santoso - Bulan 9 (September 2026) -> UNPAID
        $reading2 = MeterReading::create([
            'customer_id' => $cust1->id,
            'admin_id' => $admin->id,
            'period_month' => 9,
            'period_year' => 2026,
            'initial_meter' => 18,
            'final_meter' => 35,
            'total_usage' => 17,
            'reading_date' => Carbon::create(2026, 9, 25),
        ]);
        Bill::create([
            'bill_no' => 'TAG-202609-0001',
            'customer_id' => $cust1->id,
            'meter_reading_id' => $reading2->id,
            'period_month' => 9,
            'period_year' => 2026,
            'rate_per_m3' => 5000.00,
            'total_usage' => 17,
            'usage_cost' => 17 * 5000.00,
            'abodemen_cost' => 5000.00,
            'total_amount' => (17 * 5000.00) + 5000.00,
            'due_date' => Carbon::create(2026, 10, 20),
            'status' => 'unpaid',
        ]);

        // Siti Rahayu - Bulan 9 (September 2026) -> UNPAID
        $reading3 = MeterReading::create([
            'customer_id' => $cust2->id,
            'admin_id' => $admin->id,
            'period_month' => 9,
            'period_year' => 2026,
            'initial_meter' => 0,
            'final_meter' => 12,
            'total_usage' => 12,
            'reading_date' => Carbon::create(2026, 9, 25),
        ]);
        Bill::create([
            'bill_no' => 'TAG-202609-0002',
            'customer_id' => $cust2->id,
            'meter_reading_id' => $reading3->id,
            'period_month' => 9,
            'period_year' => 2026,
            'rate_per_m3' => 5000.00,
            'total_usage' => 12,
            'usage_cost' => 12 * 5000.00,
            'abodemen_cost' => 5000.00,
            'total_amount' => (12 * 5000.00) + 5000.00,
            'due_date' => Carbon::create(2026, 10, 20),
            'status' => 'unpaid',
        ]);
    }
}
