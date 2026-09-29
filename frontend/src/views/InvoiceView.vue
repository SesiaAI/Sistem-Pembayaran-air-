<template>
  <div class="min-h-screen bg-slate-100 py-8 px-4 sm:px-6">
    <!-- Action Bar (Hidden on print) -->
    <div class="max-w-2xl mx-auto mb-6 flex items-center justify-between print:hidden">
      <button 
        @click="$router.back()"
        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-xs transition"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Kembali</span>
      </button>

      <button 
        @click="printReceipt"
        class="inline-flex items-center gap-2 text-sm font-bold text-white bg-sky-600 hover:bg-sky-700 px-5 py-2.5 rounded-xl shadow-md shadow-sky-600/20 transition"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        <span>Cetak Kuitansi</span>
      </button>
    </div>

    <!-- Receipt / Invoice Card -->
    <div v-if="loading" class="text-center py-20">
      <svg class="animate-spin h-8 w-8 text-sky-600 mx-auto" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <div 
      v-else-if="bill" 
      id="receipt-print-area" 
      class="max-w-2xl mx-auto bg-white rounded-3xl shadow-xl shadow-slate-200 border border-slate-200 p-8 sm:p-10 relative overflow-hidden"
    >
      <!-- Watermark Status Lunas -->
      <div 
        v-if="bill.status === 'paid'"
        class="absolute right-6 top-24 pointer-events-none select-none border-4 border-emerald-500/30 text-emerald-600/30 font-black text-4xl sm:text-5xl uppercase tracking-widest px-6 py-2 rounded-2xl rotate-[-12deg]"
      >
        LUNAS
      </div>

      <!-- Receipt Header -->
      <div class="flex items-center justify-between border-b border-slate-200 pb-6 mb-6">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-cyan-500 flex items-center justify-center text-white shadow-md">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">TirtaPay</h2>
            <p class="text-xs text-slate-500">Kuitansi Pembayaran Tagihan Air Bersih</p>
          </div>
        </div>

        <div class="text-right">
          <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">No. Bukti / Invoice</div>
          <div class="text-sm font-mono font-bold text-slate-800">{{ bill.bill_no }}</div>
        </div>
      </div>

      <!-- Customer & Bill Info Grid -->
      <div class="grid grid-cols-2 gap-4 text-xs text-slate-600 bg-slate-50 p-5 rounded-2xl border border-slate-200/80 mb-6">
        <div>
          <span class="block text-slate-400 uppercase font-semibold text-[10px]">Nama Pelanggan</span>
          <span class="font-bold text-slate-900 text-sm">{{ bill.customer?.user?.name }}</span>
          <span class="block text-slate-500 mt-1">{{ bill.customer?.address }}</span>
        </div>
        <div>
          <span class="block text-slate-400 uppercase font-semibold text-[10px]">No. Sambungan & Meter</span>
          <span class="font-bold text-slate-900 text-sm">{{ bill.customer?.customer_no }}</span>
          <span class="block text-slate-500 mt-1">Seri: {{ bill.customer?.meter_number || '-' }}</span>
        </div>
        <div>
          <span class="block text-slate-400 uppercase font-semibold text-[10px]">Periode Pemakaian</span>
          <span class="font-bold text-slate-900">{{ formatPeriod(bill.period_month, bill.period_year) }}</span>
        </div>
        <div>
          <span class="block text-slate-400 uppercase font-semibold text-[10px]">Jatuh Tempo</span>
          <span class="font-bold text-rose-600">{{ formatDate(bill.due_date) }}</span>
        </div>
      </div>

      <!-- Breakdown Table -->
      <table class="w-full text-xs text-left mb-6">
        <thead>
          <tr class="border-b border-slate-200 text-slate-400 uppercase font-bold text-[10px]">
            <th class="py-2.5">Keterangan</th>
            <th class="py-2.5 text-center">Stand Meter</th>
            <th class="py-2.5 text-center">Volume (m³)</th>
            <th class="py-2.5 text-right">Tarif</th>
            <th class="py-2.5 text-right">Subtotal</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <tr>
            <td class="py-3 font-semibold text-slate-900">Pemakaian Air</td>
            <td class="py-3 text-center text-slate-600">
              {{ bill.meter_reading?.initial_meter || 0 }} → {{ bill.meter_reading?.final_meter }}
            </td>
            <td class="py-3 text-center font-bold">{{ bill.total_usage }} m³</td>
            <td class="py-3 text-right">Rp {{ formatRupiah(bill.rate_per_m3) }}</td>
            <td class="py-3 text-right font-semibold">Rp {{ formatRupiah(bill.usage_cost) }}</td>
          </tr>
          <tr>
            <td class="py-3 font-semibold text-slate-900" colspan="3">Biaya Beban Tetap (Abodemen)</td>
            <td class="py-3 text-right">-</td>
            <td class="py-3 text-right font-semibold">Rp {{ formatRupiah(bill.abodemen_cost) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="border-t-2 border-slate-800 text-slate-900 font-extrabold text-sm">
            <td class="pt-4" colspan="3">Total yang Harus Dibayar</td>
            <td class="pt-4 text-right" colspan="2">
              <span class="text-lg text-sky-700">Rp {{ formatRupiah(bill.total_amount) }}</span>
            </td>
          </tr>
        </tfoot>
      </table>

      <!-- Status Pembayaran Box -->
      <div 
        :class="bill.status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'"
        class="p-4 rounded-2xl border text-xs flex items-center justify-between"
      >
        <div>
          <span class="font-bold block text-sm">
            {{ bill.status === 'paid' ? 'STATUS: SUDAH LUNAS' : 'STATUS: BELUM LUNAS' }}
          </span>
          <span v-if="bill.latest_payment?.paid_at" class="text-slate-600 block mt-0.5">
            Dibayar pada: {{ formatDate(bill.latest_payment.paid_at) }} • {{ bill.latest_payment.payment_method_detail || 'Online Payment' }}
          </span>
          <span v-else class="text-slate-600 block mt-0.5">
            Mohon lunasi tagihan sebelum tanggal jatuh tempo.
          </span>
        </div>

        <span 
          :class="bill.status === 'paid' ? 'bg-emerald-600' : 'bg-rose-600'"
          class="w-3 h-3 rounded-full"
        ></span>
      </div>

      <!-- Footer Receipt Note -->
      <div class="mt-8 text-center text-[10px] text-slate-400">
        <p>Struk ini merupakan bukti pembayaran tagihan air yang sah diterbitkan oleh sistem komputerisasi TirtaPay.</p>
        <p class="mt-0.5">Simpan bukti pembayaran ini untuk keperluan verifikasi jika diperlukan.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../api';

const route = useRoute();
const bill = ref(null);
const loading = ref(true);

const fetchBill = async () => {
  loading.value = true;
  try {
    const res = await api.get(`/bills/${route.params.id}`);
    bill.value = res.data;
  } catch (err) {
    alert('Gagal memuat rincian tagihan.');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchBill();
});

const printReceipt = () => {
  window.print();
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
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<style>
@media print {
  body {
    background: white !important;
  }
  #receipt-print-area {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    max-width: 100% !important;
  }
}
</style>
