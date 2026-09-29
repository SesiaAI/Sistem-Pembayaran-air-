<template>
  <div class="flex min-h-screen bg-slate-50">
    <AdminSidebar />

    <main class="flex-1 p-6 lg:p-10 space-y-8 overflow-y-auto">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Manajemen Pengguna & Admin</h1>
          <p class="text-sm text-slate-500 mt-1">Kelola hak akses akun petugas lapangan dan super admin</p>
        </div>

        <button 
          @click="openAddModal"
          class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl shadow-md shadow-sky-600/20 text-sm flex items-center gap-2 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Tambah Admin Baru</span>
        </button>
      </div>

      <!-- Users Table -->
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
                <th class="py-3.5 px-4">Nama User</th>
                <th class="py-3.5 px-4">No. HP</th>
                <th class="py-3.5 px-4">Role Akses</th>
                <th class="py-3.5 px-4">Tanggal Daftar</th>
                <th class="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/80">
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ user.name }}</td>
                <td class="py-3.5 px-4 font-mono text-slate-600">{{ user.phone }}</td>
                <td class="py-3.5 px-4">
                  <span 
                    :class="{
                      'bg-purple-50 text-purple-700 border-purple-200': user.role === 'super_admin',
                      'bg-sky-50 text-sky-700 border-sky-200': user.role === 'admin',
                      'bg-slate-100 text-slate-700 border-slate-200': user.role === 'pelanggan'
                    }"
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize"
                  >
                    {{ formatRole(user.role) }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-slate-500">{{ formatDate(user.created_at) }}</td>
                <td class="py-3.5 px-4 text-right space-x-2">
                  <button 
                    v-if="user.id !== auth.user?.id"
                    @click="deleteUser(user.id)"
                    class="text-rose-600 hover:text-rose-800 font-bold transition"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add Admin Modal -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 p-6 sm:p-8">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
            <h3 class="text-lg font-bold text-slate-900">Tambah Akun Admin Baru</h3>
            <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveAdmin" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Petugas</label>
              <input 
                v-model="form.name" 
                type="text" 
                required
                placeholder="Nama admin"
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium outline-none focus:border-sky-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor HP</label>
              <input 
                v-model="form.phone" 
                type="text" 
                required
                placeholder="0812..."
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium outline-none focus:border-sky-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Role Petugas</label>
              <select 
                v-model="form.role"
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium outline-none bg-white"
              >
                <option value="admin">Admin Lapangan (Catat Meter & Kasir)</option>
                <option value="super_admin">Super Admin (Akses Penuh)</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Default</label>
              <input 
                v-model="form.password" 
                type="password" 
                required
                placeholder="password"
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium outline-none focus:border-sky-500"
              />
            </div>

            <div class="pt-4 flex justify-end gap-3">
              <button 
                type="button" 
                @click="isModalOpen = false"
                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition"
              >
                Batal
              </button>
              <button 
                type="submit" 
                :disabled="saving"
                class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-xs shadow-md shadow-sky-600/20 transition flex items-center gap-2"
              >
                <span>Simpan Akun Admin</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import AdminSidebar from '../../components/AdminSidebar.vue';
import api from '../../api';

const auth = useAuthStore();
const users = ref([]);
const loading = ref(true);

const isModalOpen = ref(false);
const saving = ref(false);

const form = ref({
  name: '',
  phone: '',
  role: 'admin',
  password: 'password',
});

const fetchUsers = async () => {
  loading.value = true;
  try {
    const res = await api.get('/users?per_page=50');
    users.value = res.data.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  form.value = {
    name: '',
    phone: '',
    role: 'admin',
    password: 'password',
  };
  isModalOpen.value = true;
};

const saveAdmin = async () => {
  saving.value = true;
  try {
    await api.post('/users', form.value);
    alert('Akun admin berhasil dibuat.');
    isModalOpen.value = false;
    fetchUsers();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal membuat akun.');
  } finally {
    saving.value = false;
  }
};

const deleteUser = async (id) => {
  if (!confirm('Apakah Anda yakin ingin menghapus akun ini?')) return;
  try {
    await api.delete(`/users/${id}`);
    alert('Akun berhasil dihapus.');
    fetchUsers();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus akun.');
  }
};

const formatRole = (role) => {
  if (role === 'super_admin') return 'Super Admin';
  if (role === 'admin') return 'Admin Lapangan';
  return 'Pelanggan';
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

onMounted(() => {
  fetchUsers();
});
</script>
