<template>
  <div class="min-h-screen bg-slate-50 flex flex-col">
    <Navbar />

    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
      <div class="max-w-md w-full">
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8 sm:p-10">
          <div class="text-center mb-8">
            <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-2xl mx-auto flex items-center justify-center mb-3 border border-sky-100">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
              </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pendaftaran Warga</h1>
            <p class="text-sm text-slate-500 mt-1">Daftarkan identitas Anda untuk aktivasi akun tagihan air</p>
          </div>

          <div v-if="errorMessage" class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ errorMessage }}</span>
          </div>

          <form @submit.prevent="handleRegister" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap Sesuai KTP</label>
              <input 
                v-model="form.name" 
                type="text" 
                placeholder="Contoh: Budi Santoso" 
                required
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 text-sm font-medium text-slate-800 transition outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Handphone / WhatsApp</label>
              <input 
                v-model="form.phone" 
                type="text" 
                placeholder="Contoh: 081234567890" 
                required
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 text-sm font-medium text-slate-800 transition outline-none"
              />
              <p class="mt-1 text-[11px] text-slate-400">Nomor ini akan digunakan sebagai identitas login Anda.</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Rumah / Blok / RT RW</label>
              <textarea 
                v-model="form.address" 
                rows="2"
                placeholder="Contoh: Jl. Melati No. 12, RT 01/RW 02" 
                required
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 text-sm font-medium text-slate-800 transition outline-none"
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi (Password)</label>
              <input 
                v-model="form.password" 
                type="password" 
                placeholder="Minimal 6 karakter" 
                required
                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 text-sm font-medium text-slate-800 transition outline-none"
              />
            </div>

            <button 
              type="submit" 
              :disabled="loading"
              class="w-full mt-2 py-3.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-lg shadow-sky-600/25 transition duration-150 flex items-center justify-center gap-2"
            >
              <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ loading ? 'Mendaftarkan...' : 'Daftar Sekarang' }}</span>
            </button>
          </form>

          <p class="mt-6 text-center text-xs text-slate-500">
            Sudah memiliki akun? 
            <router-link to="/login" class="font-bold text-sky-600 hover:text-sky-700 underline">
              Masuk di sini
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Navbar from '../components/Navbar.vue';

const router = useRouter();
const auth = useAuthStore();

const form = ref({
  name: '',
  phone: '',
  address: '',
  password: '',
});

const loading = ref(false);
const errorMessage = ref('');

const handleRegister = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    await auth.register(form.value.name, form.value.phone, form.value.password, form.value.address);
    router.push('/pelanggan/dashboard');
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal mendaftar. Pastikan nomor HP belum terdaftar.';
  } finally {
    loading.value = false;
  }
};
</script>
