<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
      <!-- Header -->
      <div class="bg-gradient-to-r from-sky-600 to-cyan-600 p-6 text-white text-center relative">
        <button 
          @click="closeModal" 
          class="absolute top-4 right-4 text-white/80 hover:text-white p-1 rounded-full hover:bg-white/10 transition"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <div class="w-14 h-14 bg-white/20 rounded-2xl mx-auto flex items-center justify-center mb-3">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-xl font-bold">Pembayaran Tagihan Air</h3>
        <p class="text-sky-100 text-sm mt-1">No. Tagihan: {{ bill?.bill_no }}</p>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-4">
        <!-- Rincian Biaya -->
        <div class="bg-slate-50 p-4 rounded-xl space-y-2 border border-slate-200/60 text-sm">
          <div class="flex justify-between text-slate-600">
            <span>Periode Tagihan</span>
            <span class="font-semibold text-slate-800">{{ formatPeriod(bill?.period_month, bill?.period_year) }}</span>
          </div>
          <div class="flex justify-between text-slate-600">
            <span>Pemakaian Air ({{ bill?.total_usage }} m³)</span>
            <span class="font-medium text-slate-800">Rp {{ formatRupiah(bill?.usage_cost) }}</span>
          </div>
          <div class="flex justify-between text-slate-600">
            <span>Biaya Abodemen</span>
            <span class="font-medium text-slate-800">Rp {{ formatRupiah(bill?.abodemen_cost) }}</span>
          </div>
          <div class="pt-2 border-t border-slate-200 flex justify-between font-bold text-base text-sky-700">
            <span>Total Tagihan</span>
            <span>Rp {{ formatRupiah(bill?.total_amount) }}</span>
          </div>
        </div>

        <!-- Info Mode Midtrans -->
        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-start gap-2">
          <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <p class="font-semibold">Metode Pembayaran Online:</p>
            <p>Mendukung <strong>Midtrans Snap</strong> (QRIS, GoPay, Virtual Account BCA, BRI, Mandiri) serta <strong>Simulasi Bayar Instan</strong> untuk pengujian.</p>
          </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="space-y-2 pt-2">
          <!-- Tombol Midtrans Snap Popup -->
          <button 
            @click="payWithMidtrans"
            :disabled="loading"
            class="w-full py-3 px-4 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-semibold rounded-xl shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 transition"
          >
            <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Bayar via Midtrans (QRIS / VA)</span>
          </button>

          <!-- Tombol Simulasi Pembayaran Berhasil -->
          <button 
            @click="simulatePayment"
            :disabled="simulating"
            class="w-full py-2.5 px-4 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-xl border border-emerald-300 flex items-center justify-center gap-2 text-sm transition"
          >
            <svg v-if="simulating" class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-else>⚡ Simulasi Bayar Lunas (Testing Langsung)</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../api';

const props = defineProps({
  isOpen: Boolean,
  bill: Object,
});

const emit = defineEmits(['close', 'payment-success']);

const loading = ref(false);
const simulating = ref(false);

const closeModal = () => {
  emit('close');
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

const payWithMidtrans = async () => {
  if (!props.bill) return;
  loading.value = true;
  try {
    const res = await api.post(`/bills/${props.bill.id}/snap-token`);
    const snapToken = res.data.snap_token;
    const isMock = res.data.is_mock;

    // Jika window.snap tersedia dan bukan mock token
    if (window.snap && !isMock) {
      window.snap.pay(snapToken, {
        onSuccess: async function (result) {
          try {
            await api.post(`/bills/${props.bill.id}/sync-payment`, { result });
          } catch (e) {
            console.error('Sync payment error', e);
          }
          alert('✅ Pembayaran Midtrans berhasil diproses!');
          emit('payment-success');
          closeModal();
        },
        onPending: async function (result) {
          try {
            await api.post(`/bills/${props.bill.id}/sync-payment`, { result });
          } catch (e) {
            console.error('Sync payment pending', e);
          }
          alert('ℹ️ Pembayaran sedang diproses / menunggu transfer.');
          emit('payment-success');
          closeModal();
        },
        onError: function (result) {
          alert('Pembayaran gagal atau dibatalkan.');
        },
        onClose: async function () {
          // Periksa apakah status sudah berubah menjadi lunas
          try {
            const syncRes = await api.post(`/bills/${props.bill.id}/sync-payment`);
            if (syncRes.data?.status === 'paid') {
              emit('payment-success');
              closeModal();
            }
          } catch (e) {}
        }
      });
    } else {
      // Jika mode mock dev atau snap.js belum memuat key asli
      alert('Kunci Midtrans belum aktif. Mengalihkan ke Simulasi Bayar Otomatis.');
      await simulatePayment();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal memproses transaksi.');
  } finally {
    loading.value = false;
  }
};

const simulatePayment = async () => {
  if (!props.bill) return;
  simulating.value = true;
  try {
    const res = await api.post(`/bills/${props.bill.id}/simulate-payment`, {
      payment_type: 'qris_simulated',
    });
    alert('✅ ' + (res.data.message || 'Pembayaran berhasil disimulasikan! Tagihan telah LUNAS.'));
    emit('payment-success');
    closeModal();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal melakukan simulasi pembayaran.');
  } finally {
    simulating.value = false;
  }
};
</script>
