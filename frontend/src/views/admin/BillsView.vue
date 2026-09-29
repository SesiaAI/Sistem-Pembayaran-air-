<template>
  <div class="flex min-h-screen bg-slate-50">
    <AdminSidebar />

    <main class="flex-1 p-6 lg:p-10 space-y-8 overflow-y-auto">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Manajemen Tagihan & Kasir</h1>
        <p class="text-sm text-slate-500 mt-1">Pantau seluruh tagihan terbit, status pembayaran, dan terima pembayaran tunai di loket</p>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[200px]">
          <input 
            v-model="search"
            @input="fetchBills"
            type="text" 
            placeholder="Cari no. tagihan, nama pelanggan, no. sambungan..."
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 text-xs font-medium text-slate-800 outline-none"
          />
        </div>

        <div class="flex items-center gap-2">
          <select 
            v-model="filterStatus"
            @change="fetchBills"
            class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none bg-white"
          >
            <option value="">Semua Status</option>
            <option value="unpaid">Belum Lunas (Unpaid)</option>
            <option value="paid">Lunas (Paid)</option>
          </select>

          <select 
            v-model="filterMonth"
            @change="fetchBills"
            class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none bg-white"
          >
            <option value="">Semua Bulan</option>
            <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
          </select>

          <select 
            v-model="filterYear"
            @change="fetchBills"
            class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none bg-white"
          >
            <option value="">Semua Tahun</option>
            <option value="2026">2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div v-if="loading" class="text-center py-12">
          <svg class="animate-spin h-8 w-8 text-sky-600 mx-auto" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs text-left">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 uppercase font-bold text-[10px]">
                <th class="py-3.5 px-4">No. Tagihan</th>
                <th class="py-3.5 px-4">Pelanggan</th>
                <th class="py-3.5 px-4">Periode</th>
                <th class="py-3.5 px-4 text-center">Volume (m³)</th>
                <th class="py-3.5 px-4 text-right">Biaya Air</th>
                <th class="py-3.5 px-4 text-right">Abodemen</th>
                <th class="py-3.5 px-4 text-right">Total Tagihan</th>
                <th class="py-3.5 px-4 text-center">Status</th>
                <th class="py-3.5 px-4 text-right">Aksi Kasir</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="bill in bills" :key="bill.id" class="hover:bg-slate-50/80">
                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ bill.bill_no }}</td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ bill.customer?.user?.name }}</div>
                  <div class="text-[10px] text-slate-400">{{ bill.customer?.customer_no }}</div>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-800">
                  {{ formatPeriod(bill.period_month, bill.period_year) }}
                </td>
                <td class="py-3.5 px-4 text-center font-bold text-slate-900">
                  {{ bill.total_usage }} m³
                </td>
                <td class="py-3.5 px-4 text-right text-slate-600">
                  Rp {{ formatRupiah(bill.usage_cost) }}
                </td>
                <td class="py-3.5 px-4 text-right text-slate-600">
                  Rp {{ formatRupiah(bill.abodemen_cost) }}
                </td>
                <td class="py-3.5 px-4 text-right font-black text-sky-800 text-sm">
                  Rp {{ formatRupiah(bill.total_amount) }}
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span 
                    :class="bill.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize"
                  >
                    {{ bill.status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right space-x-1">
                  <!-- Tombol Terima Bayar Tunai -->
                  <button 
                    v-if="bill.status === 'unpaid'"
                    @click="payCash(bill)"
                    class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[11px] shadow-xs transition"
                    title="Terima bayar tunai di loket"
                  >
                    💵 Bayar Tunai
                  </button>

                  <!-- Tombol Cetak Kuitansi -->
                  <router-link 
                    :to="`/pelanggan/invoice/${bill.id}`"
                    class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-[11px] transition inline-block"
                  >
                    Cetak
                  </router-link>
                </td>
              </tr>

              <tr v-if="bills.length === 0">
                <td colspan="9" class="text-center py-10 text-slate-400">
                  Tidak ada data tagihan yang sesuai dengan filter.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminSidebar from '../../components/AdminSidebar.vue';
import api from '../../api';

const bills = ref([]);
const loading = ref(true);
const search = ref('');
const filterStatus = ref('');
const filterMonth = ref('');
const filterYear = ref('');

const fetchBills = async () => {
  loading.value = true;
  try {
    const params = {};
    if (search.value) params.search = search.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (filterMonth.value) params.month = filterMonth.value;
    if (filterYear.value) params.year = filterYear.value;

    const res = await api.get('/bills', { params });
    bills.value = res.data.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const payCash = async (bill) => {
  if (!confirm(`Konfirmasi pembayaran tunai untuk ${bill.customer?.user?.name} sebesar Rp ${formatRupiah(bill.total_amount)}?`)) return;
  try {
    await api.post(`/bills/${bill.id}/pay-cash`);
    alert('✅ Pembayaran tunai berhasil dicatat! Status tagihan sekarang LUNAS.');
    fetchBills();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal memproses pembayaran tunai.');
  }
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

const getMonthName = (month) => {
  const names = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  return names[month];
};

onMounted(() => {
  fetchBills();
});
</script>
