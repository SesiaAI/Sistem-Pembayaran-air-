<template>
  <div class="flex min-h-screen bg-slate-50">
    <AdminSidebar />

    <main class="flex-1 p-6 lg:p-10 space-y-8 overflow-y-auto">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Pengaturan Struktur Tarif Air</h1>
        <p class="text-sm text-slate-500 mt-1">Konfigurasi tarif resmi pemakaian air per kubik (m³) dan biaya beban tetap bulanan (Khusus Super Admin)</p>
      </div>

      <div class="max-w-xl bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
        <form @submit.prevent="updateTariff" class="space-y-5">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
              Tarif Pemakaian Air (Per 1 m³)
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-4 flex items-center font-bold text-slate-400 text-sm">
                Rp
              </span>
              <input 
                v-model="form.rate_per_m3" 
                type="number" 
                step="100" 
                required
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 text-base font-bold text-slate-900 outline-none"
              />
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Default di daerah Anda: Rp 5.000 per 1 m³.</p>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
              Biaya Abodemen (Biaya Beban Tetap Per Bulan)
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-4 flex items-center font-bold text-slate-400 text-sm">
                Rp
              </span>
              <input 
                v-model="form.abodemen" 
                type="number" 
                step="100" 
                required
                class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 text-base font-bold text-slate-900 outline-none"
              />
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Default: Rp 5.000 per bulan ditambahkan otomatis ke tagihan.</p>
          </div>

          <div class="p-4 bg-sky-50 rounded-2xl border border-sky-100 text-xs text-sky-900">
            <span class="font-bold">Contoh Simulasi Perhitungan:</span>
            <p class="mt-1">
              Jika warga memakai <strong>15 m³</strong> air, maka total tagihan = (15 × Rp {{ formatRupiah(form.rate_per_m3) }}) + Rp {{ formatRupiah(form.abodemen) }} = <strong>Rp {{ formatRupiah((15 * form.rate_per_m3) + Number(form.abodemen)) }}</strong>.
            </p>
          </div>

          <button 
            type="submit" 
            :disabled="saving"
            class="w-full py-3.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-lg shadow-sky-600/20 transition flex items-center justify-center gap-2"
          >
            <svg v-if="saving" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Simpan Perubahan Tarif</span>
          </button>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminSidebar from '../../components/AdminSidebar.vue';
import api from '../../api';

const form = ref({
  rate_per_m3: 5000,
  abodemen: 5000,
});

const saving = ref(false);

const fetchTariff = async () => {
  try {
    const res = await api.get('/tariffs/active');
    if (res.data) {
      form.value.rate_per_m3 = Number(res.data.rate_per_m3) || 5000;
      form.value.abodemen = Number(res.data.abodemen) || 5000;
    }
  } catch (err) {
    console.error(err);
  }
};

const updateTariff = async () => {
  saving.value = true;
  try {
    await api.post('/tariffs', form.value);
    alert('✅ Tarif berhasil diperbarui! Seluruh tagihan baru yang terbit akan menggunakan tarif ini.');
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal memperbarui tarif.');
  } finally {
    saving.value = false;
  }
};

const formatRupiah = (val) => {
  if (!val) return '0';
  return Number(val).toLocaleString('id-ID');
};

onMounted(() => {
  fetchTariff();
});
</script>
