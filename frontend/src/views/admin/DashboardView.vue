<template>
  <div class="flex min-h-screen bg-slate-50">
    <AdminSidebar />

    <main class="flex-1 p-6 lg:p-10 space-y-8 overflow-y-auto">
      <!-- Top Title -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Dashboard Operasional</h1>
          <p class="text-sm text-slate-500 mt-1">Ringkasan transaksi, distribusi air, dan performa keuangan TirtaPay</p>
        </div>

        <div class="flex items-center gap-3">
          <router-link 
            to="/admin/meter-readings"
            class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl shadow-md shadow-sky-600/20 text-sm flex items-center gap-2 transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Input Catat Meter</span>
          </router-link>
        </div>
      </div>

      <!-- Stats Grid -->
      <div v-if="loading" class="text-center py-12">
        <svg class="animate-spin h-8 w-8 text-sky-600 mx-auto" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else class="space-y-8">
        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- Card 1: Pendapatan Bulan Ini -->
          <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-4">
              <span class="text-xs font-bold uppercase tracking-wider">Pendapatan Lunas</span>
              <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="text-2xl font-black text-slate-900">
              Rp {{ formatRupiah(stats?.revenue_this_month) }}
            </div>
            <p class="text-xs text-emerald-600 font-semibold mt-1">Bulan Berjalan</p>
          </div>

          <!-- Card 2: Total Tunggakan -->
          <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-4">
              <span class="text-xs font-bold uppercase tracking-wider">Total Tunggakan</span>
              <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
            </div>
            <div class="text-2xl font-black text-rose-600">
              Rp {{ formatRupiah(stats?.unpaid_total_amount) }}
            </div>
            <p class="text-xs text-rose-500 font-semibold mt-1">{{ stats?.unpaid_bills_count }} tagihan belum lunas</p>
          </div>

          <!-- Card 3: Air Terdistribusi -->
          <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-4">
              <span class="text-xs font-bold uppercase tracking-wider">Air Terjual (m³)</span>
              <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
                </svg>
              </div>
            </div>
            <div class="text-2xl font-black text-slate-900">
              {{ stats?.water_usage_this_month }} m³
            </div>
            <p class="text-xs text-sky-600 font-semibold mt-1">Konsumsi bulan ini</p>
          </div>

          <!-- Card 4: Total Pelanggan -->
          <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-4">
              <span class="text-xs font-bold uppercase tracking-wider">Total Pelanggan</span>
              <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
            </div>
            <div class="text-2xl font-black text-slate-900">
              {{ stats?.total_customers }}
            </div>
            <p class="text-xs text-slate-500 font-semibold mt-1">Warga terdaftar</p>
          </div>
        </div>

        <!-- 6-Month Chart & Quick Shortcuts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Chart -->
          <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Performa Pendapatan 6 Bulan Terakhir</h2>
                <p class="text-xs text-slate-500">Total penerimaan kas air per periode</p>
              </div>
            </div>

            <div class="grid grid-cols-6 gap-3 items-end h-52 pt-8 pb-2 border-b border-slate-100">
              <div 
                v-for="(item, idx) in stats?.monthly_chart" 
                :key="idx" 
                class="flex flex-col items-center h-full justify-end group"
              >
                <div class="text-[10px] font-bold text-emerald-700 mb-1 opacity-80 group-hover:opacity-100 text-center">
                  {{ item.revenue > 0 ? (item.revenue / 1000).toFixed(0) + 'k' : '0' }}
                </div>
                <div 
                  class="w-full max-w-[42px] rounded-t-xl bg-gradient-to-t from-emerald-600 to-teal-400 group-hover:from-emerald-700 group-hover:to-teal-500 transition-all duration-300"
                  :style="{ height: `${Math.max(item.revenue / 1500, 8)}px`, maxHeight: '140px' }"
                ></div>
                <div class="text-[10px] font-semibold text-slate-400 mt-2 truncate w-full text-center">
                  {{ item.period }}
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions Panel -->
          <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="text-lg font-bold text-slate-900">Aksi Cepat Admin</h2>
            <p class="text-xs text-slate-500">Pintasan menu operasional lapangan dan kasir</p>

            <div class="space-y-3 pt-2">
              <router-link 
                to="/admin/meter-readings"
                class="flex items-center gap-3 p-3.5 rounded-2xl bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-100 font-semibold text-sm transition"
              >
                <div class="w-8 h-8 rounded-xl bg-sky-600 text-white flex items-center justify-center shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </div>
                <div>
                  <div class="font-bold">Input Stand Meter</div>
                  <div class="text-[11px] text-sky-600/80 font-normal">Catat stand meter warga & terbitkan tagihan</div>
                </div>
              </router-link>

              <router-link 
                to="/admin/bills"
                class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 font-semibold text-sm transition"
              >
                <div class="w-8 h-8 rounded-xl bg-slate-800 text-white flex items-center justify-center shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div>
                  <div class="font-bold">Kasir Pembayaran Tunai</div>
                  <div class="text-[11px] text-slate-500 font-normal">Verifikasi pembayaran warga langsung di loket</div>
                </div>
              </router-link>

              <router-link 
                to="/admin/customers"
                class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 font-semibold text-sm transition"
              >
                <div class="w-8 h-8 rounded-xl bg-slate-700 text-white flex items-center justify-center shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                  </svg>
                </div>
                <div>
                  <div class="font-bold">Kelola Data Pelanggan</div>
                  <div class="text-[11px] text-slate-500 font-normal">Pasang no. meteran & update alamat warga</div>
                </div>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Pembayaran Masuk Terbaru</h2>
              <p class="text-xs text-slate-500">Histori transaksi lunas (Midtrans & Tunai Loket)</p>
            </div>
            <router-link to="/admin/bills" class="text-xs font-bold text-sky-600 hover:text-sky-700">
              Lihat Semua &rarr;
            </router-link>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
              <thead>
                <tr class="border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px]">
                  <th class="py-3 px-4">Order ID</th>
                  <th class="py-3 px-4">Nama Pelanggan</th>
                  <th class="py-3 px-4">No. Tagihan</th>
                  <th class="py-3 px-4">Metode Bayar</th>
                  <th class="py-3 px-4 text-right">Jumlah</th>
                  <th class="py-3 px-4 text-right">Waktu Bayar</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="pay in stats?.recent_payments" :key="pay.id" class="hover:bg-slate-50/80">
                  <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ pay.order_id }}</td>
                  <td class="py-3 px-4 font-semibold text-slate-800">{{ pay.bill?.customer?.user?.name }}</td>
                  <td class="py-3 px-4 font-mono text-slate-500">{{ pay.bill?.bill_no }}</td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold text-[10px]">
                      {{ pay.payment_method_detail || pay.payment_type }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right font-bold text-emerald-700">
                    Rp {{ formatRupiah(pay.gross_amount) }}
                  </td>
                  <td class="py-3 px-4 text-right text-slate-500">
                    {{ formatDate(pay.paid_at) }}
                  </td>
                </tr>
                <tr v-if="!stats?.recent_payments?.length">
                  <td colspan="6" class="text-center py-6 text-slate-400">Belum ada transaksi pembayaran.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminSidebar from '../../components/AdminSidebar.vue';
import api from '../../api';

const stats = ref(null);
const loading = ref(true);

const fetchStats = async () => {
  loading.value = true;
  try {
    const res = await api.get('/admin/dashboard');
    stats.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStats();
});

const formatRupiah = (val) => {
  if (!val) return '0';
  return Number(val).toLocaleString('id-ID');
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};
</script>
