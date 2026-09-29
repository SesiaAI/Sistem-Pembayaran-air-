<template>
  <div class="min-h-screen bg-slate-50 flex flex-col">
    <Navbar />

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Tagihan Saya</h1>
          <p class="text-sm text-slate-500">Histori seluruh tagihan dan status pembayaran air Anda</p>
        </div>
        <router-link 
          to="/pelanggan/dashboard"
          class="text-sm font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1"
        >
          &larr; Kembali ke Dashboard
        </router-link>
      </div>

      <!-- Bills List -->
      <div v-if="loading" class="text-center py-12">
        <svg class="animate-spin h-8 w-8 text-sky-600 mx-auto" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else-if="bills.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-200">
        <p class="text-slate-500">Belum ada data tagihan yang diterbitkan.</p>
      </div>

      <div v-else class="space-y-4">
        <div 
          v-for="bill in bills" 
          :key="bill.id"
          class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-6"
        >
          <!-- Left Info -->
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <span 
                :class="bill.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                class="px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize"
              >
                {{ bill.status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
              </span>
              <span class="text-xs font-bold text-slate-400 font-mono">{{ bill.bill_no }}</span>
            </div>

            <h3 class="text-lg font-bold text-slate-800">
              Periode {{ formatPeriod(bill.period_month, bill.period_year) }}
            </h3>

            <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-600">
              <div>Stand: <strong class="text-slate-800">{{ bill.meter_reading?.initial_meter || 0 }} - {{ bill.meter_reading?.final_meter }}</strong></div>
              <div>Pemakaian: <strong class="text-slate-800">{{ bill.total_usage }} m³</strong></div>
              <div>Tarif Air: <strong>Rp {{ formatRupiah(bill.usage_cost) }}</strong></div>
              <div>Abodemen: <strong>Rp {{ formatRupiah(bill.abodemen_cost) }}</strong></div>
              <div>Jatuh Tempo: <strong class="text-slate-700">{{ formatDate(bill.due_date) }}</strong></div>
            </div>
          </div>

          <!-- Right Amount & Action -->
          <div class="flex items-center justify-between md:flex-col md:items-end gap-3 pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
            <div class="text-left md:text-right">
              <div class="text-[11px] uppercase font-bold text-slate-400">Total Tagihan</div>
              <div class="text-xl sm:text-2xl font-black text-sky-800">
                Rp {{ formatRupiah(bill.total_amount) }}
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button 
                v-if="bill.status === 'unpaid'"
                @click="openPaymentModal(bill)"
                class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-sm shadow-md shadow-sky-600/20 transition"
              >
                Bayar Sekarang
              </button>
              <router-link 
                :to="`/pelanggan/invoice/${bill.id}`"
                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition"
              >
                {{ bill.status === 'paid' ? 'Lihat Kuitansi' : 'Rincian' }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </main>

    <PaymentModal 
      :is-open="isModalOpen" 
      :bill="selectedBill"
      @close="isModalOpen = false"
      @payment-success="fetchBills"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Navbar from '../../components/Navbar.vue';
import PaymentModal from '../../components/PaymentModal.vue';
import api from '../../api';

const bills = ref([]);
const loading = ref(true);
const isModalOpen = ref(false);
const selectedBill = ref(null);

const fetchBills = async () => {
  loading.value = true;
  try {
    const res = await api.get('/pelanggan/bills');
    bills.value = res.data.bills || [];
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchBills();
});

const openPaymentModal = (bill) => {
  selectedBill.value = bill;
  isModalOpen.value = true;
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
