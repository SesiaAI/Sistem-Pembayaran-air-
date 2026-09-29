<template>
  <div class="flex min-h-screen bg-slate-50">
    <AdminSidebar />

    <main class="flex-1 p-6 lg:p-10 space-y-8 overflow-y-auto">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Data Pelanggan Warga</h1>
          <p class="text-sm text-slate-500 mt-1">Kelola data pelanggan, nomor sambungan pipa, dan nomor seri meteran air</p>
        </div>

        <button 
          @click="openAddModal"
          class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl shadow-md shadow-sky-600/20 text-sm flex items-center gap-2 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Tambah Pelanggan Baru</span>
        </button>
      </div>

      <!-- Search & Filter Bar -->
      <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[200px]">
          <input 
            v-model="search"
            @input="fetchCustomers"
            type="text" 
            placeholder="Cari nama, no. HP, no. sambungan, atau alamat..."
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 text-xs font-medium text-slate-800 outline-none"
          />
        </div>

        <select 
          v-model="filterStatus"
          @change="fetchCustomers"
          class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none bg-white"
        >
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>
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
                <th class="py-3.5 px-4">No. Sambungan</th>
                <th class="py-3.5 px-4">Nama Pelanggan</th>
                <th class="py-3.5 px-4">No. HP / WA</th>
                <th class="py-3.5 px-4">No. Seri Meter</th>
                <th class="py-3.5 px-4">Alamat Rumah</th>
                <th class="py-3.5 px-4 text-center">Status</th>
                <th class="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="cust in customers" :key="cust.id" class="hover:bg-slate-50/80">
                <td class="py-3.5 px-4 font-mono font-bold text-sky-800">{{ cust.customer_no }}</td>
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ cust.user?.name }}</td>
                <td class="py-3.5 px-4 font-medium text-slate-600">{{ cust.user?.phone }}</td>
                <td class="py-3.5 px-4 font-mono text-slate-600">{{ cust.meter_number || '-' }}</td>
                <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate">{{ cust.address }}</td>
                <td class="py-3.5 px-4 text-center">
                  <span 
                    :class="cust.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200'"
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize"
                  >
                    {{ cust.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right space-x-2">
                  <button 
                    @click="openEditModal(cust)"
                    class="text-sky-600 hover:text-sky-800 font-bold transition"
                  >
                    Edit
                  </button>
                  <button 
                    @click="deleteCustomer(cust.id)"
                    class="text-rose-600 hover:text-rose-800 font-bold transition"
                  >
                    Hapus
                  </button>
                </td>
              </tr>

              <tr v-if="customers.length === 0">
                <td colspan="7" class="text-center py-10 text-slate-400">
                  Tidak ada data pelanggan yang ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Tambah/Edit Pelanggan -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 p-6 sm:p-8">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
            <h3 class="text-lg font-bold text-slate-900">
              {{ editingId ? 'Edit Data Pelanggan' : 'Tambah Pelanggan Baru' }}
            </h3>
            <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveCustomer" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
              <input 
                v-model="modalForm.name" 
                type="text" 
                required
                placeholder="Nama warga"
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium outline-none focus:border-sky-500"
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor HP</label>
                <input 
                  v-model="modalForm.phone" 
                  type="text" 
                  required
                  placeholder="0812..."
                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium outline-none focus:border-sky-500"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Seri Meteran</label>
                <input 
                  v-model="modalForm.meter_number" 
                  type="text" 
                  placeholder="MTR-..."
                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium outline-none focus:border-sky-500"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Rumah</label>
              <textarea 
                v-model="modalForm.address" 
                rows="2" 
                required
                placeholder="Alamat lengkap RT/RW"
                class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium outline-none focus:border-sky-500"
              ></textarea>
            </div>

            <div v-if="editingId">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Pelanggan</label>
              <select 
                v-model="modalForm.status"
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium outline-none bg-white"
              >
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
              </select>
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
                <svg v-if="saving" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Simpan Pelanggan</span>
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
import AdminSidebar from '../../components/AdminSidebar.vue';
import api from '../../api';

const customers = ref([]);
const loading = ref(true);
const search = ref('');
const filterStatus = ref('');

const isModalOpen = ref(false);
const editingId = ref(null);
const saving = ref(false);

const modalForm = ref({
  name: '',
  phone: '',
  meter_number: '',
  address: '',
  status: 'active',
});

const fetchCustomers = async () => {
  loading.value = true;
  try {
    const params = {};
    if (search.value) params.search = search.value;
    if (filterStatus.value) params.status = filterStatus.value;

    const res = await api.get('/customers', { params });
    customers.value = res.data.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchCustomers();
});

const openAddModal = () => {
  editingId.value = null;
  modalForm.value = {
    name: '',
    phone: '',
    meter_number: '',
    address: '',
    status: 'active',
  };
  isModalOpen.value = true;
};

const openEditModal = (cust) => {
  editingId.value = cust.id;
  modalForm.value = {
    name: cust.user?.name || '',
    phone: cust.user?.phone || '',
    meter_number: cust.meter_number || '',
    address: cust.address || '',
    status: cust.status || 'active',
  };
  isModalOpen.value = true;
};

const saveCustomer = async () => {
  saving.value = true;
  try {
    if (editingId.value) {
      await api.put(`/customers/${editingId.value}`, modalForm.value);
      alert('Data pelanggan berhasil diperbarui.');
    } else {
      await api.post('/customers', modalForm.value);
      alert('Pelanggan baru berhasil ditambahkan.');
    }
    isModalOpen.value = false;
    fetchCustomers();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan data pelanggan.');
  } finally {
    saving.value = false;
  }
};

const deleteCustomer = async (id) => {
  if (!confirm('Apakah Anda yakin ingin menghapus pelanggan ini? Seluruh riwayat dan tagihannya akan terhapus.')) return;
  try {
    await api.delete(`/customers/${id}`);
    alert('Pelanggan berhasil dihapus.');
    fetchCustomers();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus.');
  }
};
</script>
