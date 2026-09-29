<template>
  <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
        <!-- Logo Brand -->
        <router-link to="/" class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-cyan-500 flex items-center justify-center text-white shadow-md shadow-sky-500/20">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
            </svg>
          </div>
          <div>
            <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-sky-700 to-cyan-600 bg-clip-text text-transparent">TirtaPay</span>
            <span class="block text-[10px] text-slate-500 font-medium tracking-wide uppercase">Sistem Pembayaran Air</span>
          </div>
        </router-link>

        <!-- Right Side Nav -->
        <div class="flex items-center gap-4">
          <template v-if="auth.isAuthenticated">
            <!-- Nav Links according to role -->
            <nav class="hidden md:flex items-center gap-1 text-sm font-semibold text-slate-600">
              <template v-if="auth.isPelanggan">
                <router-link to="/pelanggan/dashboard" class="px-3 py-2 rounded-lg hover:text-sky-600 hover:bg-sky-50 transition" active-class="text-sky-600 bg-sky-50">
                  Dashboard
                </router-link>
                <router-link to="/pelanggan/bills" class="px-3 py-2 rounded-lg hover:text-sky-600 hover:bg-sky-50 transition" active-class="text-sky-600 bg-sky-50">
                  Tagihan Saya
                </router-link>
              </template>
              <template v-else-if="auth.isAdmin">
                <router-link to="/admin/dashboard" class="px-3 py-2 rounded-lg hover:text-sky-600 hover:bg-sky-50 transition" active-class="text-sky-600 bg-sky-50">
                  Panel Admin
                </router-link>
              </template>
            </nav>

            <!-- User Menu -->
            <div class="flex items-center gap-3 pl-2 border-l border-slate-200">
              <div class="text-right hidden sm:block">
                <div class="text-sm font-bold text-slate-800">{{ auth.user?.name }}</div>
                <div class="text-xs text-sky-600 font-medium capitalize">{{ formatRole(auth.user?.role) }}</div>
              </div>
              <button 
                @click="handleLogout"
                title="Keluar"
                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
              </button>
            </div>
          </template>

          <template v-else>
            <router-link 
              to="/login"
              class="text-sm font-semibold text-slate-700 hover:text-sky-600 px-3 py-2 transition"
            >
              Masuk
            </router-link>
            <router-link 
              to="/register"
              class="text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 px-4 py-2 rounded-xl shadow-sm transition"
            >
              Daftar Warga
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();

const formatRole = (role) => {
  if (role === 'super_admin') return 'Super Admin';
  if (role === 'admin') return 'Admin Lapangan';
  return 'Pelanggan';
};

const handleLogout = async () => {
  await auth.logout();
  router.push('/login');
};
</script>
