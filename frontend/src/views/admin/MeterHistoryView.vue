<template>
  <div class="flex min-h-screen bg-slate-50">
    <AdminSidebar />

    <main class="flex-1 p-6 lg:p-10 space-y-8 overflow-y-auto">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Riwayat Pencatatan Meteran</h1>
          <p class="text-sm text-slate-500 mt-1">Daftar rekap stand meter air seluruh pelanggan yang telah dicatat</p>
        </div>

        <router-link 
          to="/admin/meter-readings"
          class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl shadow-md shadow-sky-600/20 text-sm flex items-center gap-2 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Catat Meter Baru</span>
        </router-link>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[200px]">
          <input 
            v-model="search"
            @input="fetchReadings"
            type="text" 
            placeholder="Cari nama, no. sambungan, atau no. meter..."
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 text-xs font-medium text-slate-800 outline-none"
          />
        </div>

        <div class="flex items-center gap-2">
          <select 
            v-model="filterMonth"
            @change="fetchReadings"
            class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none bg-white"
          >
            <option value="">Semua Bulan</option>
            <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
          </select>

          <select 
            v-model="filterYear"
            @change="fetchReadings"
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
                <th class="py-3.5 px-4">Periode</th>
                <th class="py-3.5 px-4">Pelanggan</th>
                <th class="py-3.5 px-4">No. Sambungan</th>
                <th class="py-3.5 px-4 text-center">Stand Awal</th>
                <th class="py-3.5 px-4 text-center">Stand Akhir</th>
                <th class="py-3.5 px-4 text-center">Volume (m³)</th>
                <th class="py-3.5 px-4">Pencatat</th>
                <th class="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="reading in readings" :key="reading.id" class="hover:bg-slate-50/80">
                <td class="py-3.5 px-4 font-bold text-slate-900">
                  {{ formatPeriod(reading.period_month, reading.period_year) }}
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ reading.customer?.user?.name }}</div>
                  <div class="text-[10px] text-slate-400">{{ reading.customer?.meter_number || 'Meteran -' }}</div>
                </td>
                <td class="py-3.5 px-4 font-mono font-semibold text-slate-600">
                  {{ reading.customer?.customer_no }}
                </td>
                <td class="py-3.5 px-4 text-center font-mono">{{ reading.initial_meter }}</td>
                <td class="py-3.5 px-4 text-center font-mono font-bold text-sky-700">{{ reading.final_meter }}</td>
                <td class="py-3.5 px-4 text-center font-bold text-slate-900">
                  {{ reading.total_usage }} m³
                </td>
                <td class="py-3.5 px-4 text-slate-500">
                  {{ reading.admin?.name || 'Admin' }}
                </td>
                <td class="py-3.5 px-4 text-right">
                  <button 
                    v-if="reading.bill?.status !== 'paid'"
                    @click="deleteReading(reading.id)"
                    class="text-rose-600 hover:text-rose-800 font-bold p-1 hover:bg-rose-50 rounded-lg transition"
                    title="Hapus pencatatan"
                  >
                    Hapus
                  </button>
                  <span v-else class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                    Lunas
                  </span>
                </td>
              </tr>

              <tr v-if="readings.length === 0">
                <td colspan="8" class="text-center py-10 text-slate-400">
                  Tidak ada data pencatatan meter yang ditemukan.
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

const readings = ref([]);
const loading = ref(true);
const search = ref('');
const filterMonth = ref('');
const filterYear = ref('');

const fetchReadings = async () => {
  loading.value = true;
  try {
    const params = {};
    if (search.value) params.search = search.value;
    if (filterMonth.value) params.month = filterMonth.value;
    if (filterYear.value) params.year = filterYear.value;

    const res = await api.get('/meter-readings', { params });
    readings.value = res.data.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchReadings();
});

const deleteReading = async (id) => {
  if (!confirm('Apakah Anda yakin ingin menghapus pencatatan meter ini? Tagihan terkait juga akan terhapus.')) return;
  try {
    await api.delete(`/meter-readings/${id}`);
    alert('Pencatatan berhasil dihapus.');
    fetchReadings();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus.');
  }
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
</script>
