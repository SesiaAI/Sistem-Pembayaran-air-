<template>
  <div class="flex min-h-screen bg-slate-50">
    <AdminSidebar />

    <main class="flex-1 p-6 lg:p-10 space-y-8 overflow-y-auto">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Input Pencatatan Stand Meter</h1>
        <p class="text-sm text-slate-500 mt-1">Catat angka meteran fisik pelanggan bulanan untuk menerbitkan tagihan otomatis</p>
      </div>

      <!-- Success Notification -->
      <div v-if="successMessage" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <div class="font-bold text-sm">Berhasil Dicatat!</div>
            <div class="text-xs">{{ successMessage }}</div>
          </div>
        </div>
        <router-link to="/admin/meter-history" class="text-xs font-bold text-emerald-700 underline">
          Lihat Riwayat &rarr;
        </router-link>
      </div>

      <!-- Error Notification -->
      <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-medium">{{ errorMessage }}</span>
      </div>

      <!-- Form Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
          <form @submit.prevent="submitReading" class="space-y-5">
            <!-- 1. Pilih Pelanggan -->
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Pilih Pelanggan Warga
              </label>
              <select 
                v-model="form.customer_id" 
                @change="handleCustomerChange"
                required
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 text-sm font-medium text-slate-800 transition outline-none bg-white"
              >
                <option value="" disabled>-- Pilih Pelanggan --</option>
                <option v-for="cust in customers" :key="cust.id" :value="cust.id">
                  {{ cust.customer_no }} - {{ cust.user?.name }} ({{ cust.meter_number || 'No Seri -' }})
                </option>
              </select>
            </div>

            <!-- Customer Details Preview -->
            <div v-if="selectedCustomer" class="p-4 rounded-2xl bg-sky-50/60 border border-sky-100 text-xs text-slate-700 grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <span class="block text-slate-400 font-semibold uppercase text-[10px]">No. Telepon / WA</span>
                <span class="font-bold text-slate-900">{{ selectedCustomer.user?.phone }}</span>
              </div>
              <div>
                <span class="block text-slate-400 font-semibold uppercase text-[10px]">No. Seri Meteran</span>
                <span class="font-bold text-slate-900">{{ selectedCustomer.meter_number || 'Belum Terpasang' }}</span>
              </div>
              <div>
                <span class="block text-slate-400 font-semibold uppercase text-[10px]">Alamat Rumah</span>
                <span class="font-bold text-slate-900 truncate block">{{ selectedCustomer.address }}</span>
              </div>
            </div>

            <!-- 2. Periode Bulan & Tahun -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Bulan Tagihan</label>
                <select 
                  v-model="form.period_month" 
                  required
                  class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 text-sm font-medium text-slate-800 transition outline-none bg-white"
                >
                  <option v-for="m in 12" :key="m" :value="m">
                    {{ getMonthName(m) }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tahun Tagihan</label>
                <input 
                  v-model="form.period_year" 
                  type="number" 
                  min="2024" 
                  max="2030" 
                  required
                  class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 text-sm font-medium text-slate-800 transition outline-none"
                />
              </div>
            </div>

            <!-- 3. Stand Meter Awal & Stand Meter Akhir -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                  Stand Meter Lalu (Awal)
                </label>
                <input 
                  v-model="form.initial_meter" 
                  type="number" 
                  readonly
                  class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 font-bold text-sm cursor-not-allowed outline-none"
                />
                <span class="text-[11px] text-slate-400 mt-1 block">Otomatis ditarik dari pencatatan bulan lalu</span>
              </div>

              <div>
                <label class="block text-xs font-bold text-sky-700 uppercase tracking-wider mb-2">
                  Stand Meter Sekarang (Akhir) *
                </label>
                <input 
                  v-model="form.final_meter" 
                  type="number" 
                  :min="form.initial_meter"
                  placeholder="Masukkan angka meteran" 
                  required
                  class="w-full px-4 py-3 rounded-xl border-2 border-sky-500 focus:ring-4 focus:ring-sky-500/20 text-base font-extrabold text-slate-900 transition outline-none"
                />
              </div>
            </div>

            <!-- 4. Upload Foto Bukti & Catatan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                  Foto Angka Meteran Fisik (Opsional)
                </label>
                <input 
                  type="file" 
                  accept="image/*"
                  @change="handlePhotoUpload"
                  class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                  Catatan Lapangan (Opsional)
                </label>
                <input 
                  v-model="form.notes" 
                  type="text" 
                  placeholder="Contoh: Meteran air jernih, pipa normal" 
                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-800 transition outline-none"
                />
              </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
              <button 
                type="submit" 
                :disabled="loading || computedUsage < 0 || !form.customer_id"
                class="w-full py-4 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-extrabold rounded-2xl shadow-lg shadow-sky-600/25 transition duration-150 flex items-center justify-center gap-2 text-base"
              >
                <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Simpan Pencatatan & Terbitkan Tagihan</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Live Calculation Preview Card (1 Col) -->
        <div class="bg-gradient-to-br from-slate-900 to-sky-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col justify-between">
          <div class="space-y-6">
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
              <div class="text-xs font-bold uppercase tracking-wider text-sky-400">Kalkulasi Tagihan Otomatis</div>
              <span class="px-2.5 py-0.5 rounded-full bg-sky-500/20 text-sky-300 text-[10px] font-bold border border-sky-400/20">
                Live Preview
              </span>
            </div>

            <!-- Kubikasi -->
            <div>
              <div class="text-xs text-slate-400">Volume Pemakaian Air</div>
              <div class="text-4xl font-black text-white mt-1">
                {{ computedUsage }} <span class="text-lg font-normal text-sky-300">m³</span>
              </div>
              <p class="text-xs text-slate-400 mt-1">
                Rumus: Stand Akhir ({{ form.final_meter || 0 }}) - Stand Awal ({{ form.initial_meter }})
              </p>
            </div>

            <!-- Breakdown -->
            <div class="space-y-3 bg-white/5 p-4 rounded-2xl border border-white/10 text-xs">
              <div class="flex justify-between">
                <span class="text-slate-300">Biaya Air ({{ computedUsage }} m³ × Rp 5.000)</span>
                <span class="font-bold">Rp {{ formatRupiah(computedWaterCost) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-300">Biaya Abodemen Bulanan</span>
                <span class="font-bold">Rp 5.000</span>
              </div>
            </div>

            <!-- Total -->
            <div class="pt-4 border-t border-white/10">
              <div class="text-xs text-slate-400 uppercase font-semibold">Total Tagihan Yang Akan Terbit</div>
              <div class="text-3xl font-black text-cyan-400 mt-1">
                Rp {{ formatRupiah(computedTotal) }}
              </div>
            </div>
          </div>

          <div class="mt-8 text-[11px] text-slate-400 bg-white/5 p-3 rounded-xl">
            💡 Begitu Anda klik Simpan, tagihan langsung otomatis masuk ke akun pelanggan dan siap dibayar melalui QRIS / Kasir.
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminSidebar from '../../components/AdminSidebar.vue';
import api from '../../api';

const customers = ref([]);
const selectedCustomer = ref(null);
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const form = ref({
  customer_id: '',
  period_month: new Date().getMonth() + 1,
  period_year: new Date().getFullYear(),
  initial_meter: 0,
  final_meter: '',
  notes: '',
  photo: null,
});

const fetchCustomers = async () => {
  try {
    const res = await api.get('/customers?per_page=100');
    customers.value = res.data.data || [];
  } catch (err) {
    console.error(err);
  }
};

const handleCustomerChange = async () => {
  if (!form.value.customer_id) return;
  selectedCustomer.value = customers.value.find(c => c.id === form.value.customer_id) || null;

  try {
    const res = await api.get(`/meter-readings/latest/${form.value.customer_id}`);
    form.value.initial_meter = res.data.initial_meter || 0;
    // Set default final_meter if empty
    if (!form.value.final_meter) {
      form.value.final_meter = form.value.initial_meter;
    }
  } catch (err) {
    console.error(err);
  }
};

const handlePhotoUpload = (event) => {
  form.value.photo = event.target.files[0] || null;
};

const computedUsage = computed(() => {
  const fin = Number(form.value.final_meter) || 0;
  const init = Number(form.value.initial_meter) || 0;
  return Math.max(0, fin - init);
});

const computedWaterCost = computed(() => {
  return computedUsage.value * 5000;
});

const computedTotal = computed(() => {
  return computedWaterCost.value + 5000;
});

const submitReading = async () => {
  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  const formData = new FormData();
  formData.append('customer_id', form.value.customer_id);
  formData.append('period_month', form.value.period_month);
  formData.append('period_year', form.value.period_year);
  formData.append('initial_meter', form.value.initial_meter);
  formData.append('final_meter', form.value.final_meter);
  if (form.value.notes) formData.append('notes', form.value.notes);
  if (form.value.photo) formData.append('photo', form.value.photo);

  try {
    const res = await api.post('/meter-readings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    successMessage.value = res.data.message || 'Pencatatan berhasil disimpan dan tagihan telah terbit.';
    // Reset form
    form.value.customer_id = '';
    form.value.final_meter = '';
    form.value.notes = '';
    form.value.photo = null;
    selectedCustomer.value = null;
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal menyimpan pencatatan meter.';
  } finally {
    loading.value = false;
  }
};

const formatRupiah = (val) => {
  if (!val) return '0';
  return Number(val).toLocaleString('id-ID');
};

const getMonthName = (month) => {
  const names = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  return names[month];
};

onMounted(() => {
  fetchCustomers();
});
</script>
