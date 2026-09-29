<template>
  <div class="min-h-screen bg-slate-50 flex flex-col">
    <Navbar />

    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
      <div class="max-w-md w-full">
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8 sm:p-10">
          <div class="text-center mb-8">
            <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-2xl mx-auto flex items-center justify-center mb-3 border border-sky-100">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
              </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Selamat Datang</h1>
            <p class="text-sm text-slate-500 mt-1">Masuk dengan Nomor HP untuk mengakses akun Anda</p>
          </div>

          <!-- Alert Error -->
          <div v-if="errorMessage" class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ errorMessage }}</span>
          </div>

          <!-- Form Login -->
          <form @submit.prevent="handleLogin" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Handphone / WhatsApp</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </span>
                <input 
                  v-model="form.phone" 
                  type="text" 
                  placeholder="Contoh: 081234567890" 
                  required
                  class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 text-sm font-medium text-slate-800 transition outline-none"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </span>
                <input 
                  v-model="form.password" 
                  type="password" 
                  placeholder="••••••••" 
                  required
                  class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 text-sm font-medium text-slate-800 transition outline-none"
                />
              </div>
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
              <span>{{ loading ? 'Memproses...' : 'Masuk Sekarang' }}</span>
            </button>
          </form>

          <!-- Quick Test Selector -->
          <div class="mt-6 pt-6 border-t border-slate-100">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center mb-3">
              ⚡ Akun Coba Cepat (Demo Testing)
            </div>
            <div class="grid grid-cols-3 gap-2">
              <button 
                type="button"
                @click="fillCredentials('081234567890', 'password')"
                class="py-2 px-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-[11px] font-bold text-slate-700 text-center transition"
              >
                Super Admin
              </button>
              <button 
                type="button"
                @click="fillCredentials('081234567891', 'password')"
                class="py-2 px-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-[11px] font-bold text-slate-700 text-center transition"
              >
                Admin Lapangan
              </button>
              <button 
                type="button"
                @click="fillCredentials('081234567892', 'password')"
                class="py-2 px-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-[11px] font-bold text-slate-700 text-center transition"
              >
                Pelanggan
              </button>
            </div>
          </div>

          <!-- Register Link -->
          <p class="mt-6 text-center text-xs text-slate-500">
            Belum punya akun warga? 
            <router-link to="/register" class="font-bold text-sky-600 hover:text-sky-700 underline">
              Daftar di sini
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
  phone: '',
  password: '',
});

const loading = ref(false);
const errorMessage = ref('');

const fillCredentials = (phone, pass) => {
  form.value.phone = phone;
  form.value.password = pass;
};

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await auth.login(form.value.phone, form.value.password);
    const user = res.user;

    if (user.role === 'pelanggan') {
      router.push('/pelanggan/dashboard');
    } else {
      router.push('/admin/dashboard');
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Nomor HP atau kata sandi tidak sesuai.';
  } finally {
    loading.value = false;
  }
};
</script>
