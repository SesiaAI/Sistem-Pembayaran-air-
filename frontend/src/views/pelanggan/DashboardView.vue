<template>
  <div class="min-h-screen bg-slate-50 flex flex-col">
    <Navbar />

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Greeting & Customer Profile Card -->
      <div class="bg-gradient-to-r from-sky-700 via-sky-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-sky-900/10 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-sky-200 text-xs font-semibold mb-3 border border-white/10">
              <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              Pelanggan Aktif
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Halo, {{ auth.user?.name }}</h1>
            <p class="text-sky-200 text-sm mt-1">Alamat: {{ customer?.address || 'Belum diisi' }}</p>
          </div>

          <div class="flex flex-wrap gap-3">
            <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/15">
              <div class="text-[10px] uppercase font-bold text-sky-300">No. Sambungan</div>
              <div class="text-base font-extrabold">{{ customer?.customer_no || 'PLG-NEW' }}</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/15">
              <div class="text-[10px] uppercase font-bold text-sky-300">No. Seri Meter</div>
              <div class="text-base font-extrabold">{{ customer?.meter_number || 'Belum Terpasang' }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tagihan Aktif (Unpaid Bill Banner) -->
      <section v-if="dashboardData?.active_bill" class="bg-white rounded-3xl border-2 border-amber-300/80 p-6 sm:p-8 shadow-xl shadow-amber-500/5 relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
          <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              Tagihan Belum Dibayar
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900">
              Periode {{ formatPeriod(dashboardData.active_bill.period_month, dashboardData.active_bill.period_year) }}
            </h2>
            <p class="text-sm text-slate-500">
              Jatuh Tempo: <span class="font-semibold text-rose-600">{{ formatDate(dashboardData.active_bill.due_date) }}</span>
            </p>

            <div class="pt-2 flex flex-wrap gap-4 text-sm text-slate-600">
              <div>Pemakaian: <strong class="text-slate-800">{{ dashboardData.active_bill.total_usage }} m³</strong></div>
              <div>• Biaya Air: <strong class="text-slate-800">Rp {{ formatRupiah(dashboardData.active_bill.usage_cost) }}</strong></div>
              <div>• Abodemen: <strong class="text-slate-800">Rp {{ formatRupiah(dashboardData.active_bill.abodemen_cost) }}</strong></div>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-end justify-between gap-4 shrink-0">
            <div class="text-left lg:text-right">
              <div class="text-xs text-slate-400 font-semibold uppercase">Total Pembayaran</div>
              <div class="text-3xl sm:text-4xl font-black text-sky-700">
                Rp {{ formatRupiah(dashboardData.active_bill.total_amount) }}
              </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
              <button 
                @click="openPaymentModal(dashboardData.active_bill)"
                class="flex-1 sm:flex-none px-6 py-3 rounded-xl font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-lg shadow-sky-600/25 flex items-center justify-center gap-2 transition"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Bayar Sekarang</span>
              </button>
              <router-link 
                :to="`/pelanggan/invoice/${dashboardData.active_bill.id}`"
                class="px-4 py-3 rounded-xl font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition text-sm"
              >
                Rincian
              </router-link>
            </div>
          </div>
        </div>
      </section>

      <!-- All Caught Up Banner (If No Unpaid Bill) -->
      <section v-else class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center shadow-xs">
        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-3xl mx-auto flex items-center justify-center mb-3 border border-emerald-100">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-900">Tidak Ada Tagihan Tertunggak</h2>
        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
          Terima kasih! Seluruh tagihan air Anda telah lunas. Tagihan periode berikutnya akan diterbitkan pada akhir bulan.
        </p>
      </section>

      <!-- Riwayat Konsumsi Air 6 Bulan Terakhir (Chart) -->
      <section class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-lg font-bold text-slate-900">Riwayat Pemakaian Air</h2>
            <p class="text-xs text-slate-500">Volume pemakaian dalam satuan meter kubik (m³)</p>
          </div>
          <router-link to="/pelanggan/bills" class="text-xs font-bold text-sky-600 hover:text-sky-700">
            Lihat Semua Tagihan &rarr;
          </router-link>
        </div>

        <div v-if="dashboardData?.history_chart?.length" class="space-y-4">
          <div class="grid grid-cols-6 gap-2 sm:gap-4 items-end h-44 pt-6 pb-2 border-b border-slate-100">
            <div 
              v-for="(item, idx) in dashboardData.history_chart" 
              :key="idx" 
              class="flex flex-col items-center h-full justify-end group"
            >
              <div class="text-[11px] font-bold text-sky-700 mb-1 opacity-80 group-hover:opacity-100">
                {{ item.usage }} m³
              </div>
              <div 
                class="w-full max-w-[48px] rounded-t-xl bg-gradient-to-t from-sky-600 to-cyan-400 group-hover:from-sky-700 group-hover:to-cyan-500 transition-all duration-300"
                :style="{ height: `${Math.max(item.usage * 5, 8)}px`, maxHeight: '120px' }"
              ></div>
              <div class="text-[10px] sm:text-xs text-slate-400 font-semibold mt-2 truncate w-full text-center">
                {{ item.period }}
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8 text-slate-400 text-sm">
          Belum ada data riwayat pemakaian air.
        </div>
      </section>
    </main>

    <!-- Modal Pembayaran -->
    <PaymentModal 
      :is-open="isModalOpen" 
      :bill="selectedBill"
      @close="isModalOpen = false"
      @payment-success="handlePaymentSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '../../stores/auth';
import Navbar from '../../components/Navbar.vue';
import PaymentModal from '../../components/PaymentModal.vue';
import api from '../../api';

const auth = useAuthStore();
const customer = computed(() => auth.user?.customer);

const dashboardData = ref(null);
const isModalOpen = ref(false);
const selectedBill = ref(null);

const fetchDashboard = async () => {
  try {
    const res = await api.get('/pelanggan/dashboard');
    dashboardData.value = res.data;
  } catch (err) {
    console.error(err);
  }
};

onMounted(() => {
  fetchDashboard();
});

const openPaymentModal = (bill) => {
  selectedBill.value = bill;
  isModalOpen.value = true;
};

const handlePaymentSuccess = () => {
  fetchDashboard();
};

const formatRupiah = (val) => {
  if (!val) return '0';
  return Number(val).toLocaleString('id-ID');
};

const formatPeriod = (month, year) => {
  if (!month || !year) return '-';
  const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  return `${monthNames[month]} ${year}`;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>
