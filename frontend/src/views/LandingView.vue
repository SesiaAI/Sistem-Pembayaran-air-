<template>
  <div class="min-h-screen bg-slate-50 flex flex-col">
    <Navbar />

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28 bg-gradient-to-b from-sky-50/60 to-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-100/80 text-sky-700 text-xs font-semibold mb-6 border border-sky-200">
            <span class="w-2 h-2 rounded-full bg-sky-600 animate-pulse"></span>
            Layanan Air Bersih Modern & Transparan
          </div>
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight">
            Bayar Tagihan Air Bersih Jadi <span class="bg-gradient-to-r from-sky-600 to-cyan-600 bg-clip-text text-transparent">Lebih Mudah & Praktis</span>
          </h1>
          <p class="mt-6 text-lg sm:text-xl text-slate-600 leading-relaxed">
            Pantau pemakaian air bulanan, cek rincian kubikasi, dan lakukan pembayaran instan via <strong>Midtrans QRIS</strong>, Virtual Account, atau tunai di loket.
          </p>

          <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <router-link 
              to="/login"
              class="px-8 py-3.5 rounded-xl font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-lg shadow-sky-600/25 transition duration-150 text-center"
            >
              Masuk Akun
            </router-link>
            <router-link 
              to="/register"
              class="px-8 py-3.5 rounded-xl font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-200 shadow-xs transition duration-150 text-center"
            >
              Daftar Pelanggan Baru
            </router-link>
          </div>
        </div>

        <!-- Tariff Card Highlight -->
        <div class="mt-16 max-w-2xl mx-auto bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80 p-6 sm:p-8">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
            <div>
              <h2 class="text-xl font-bold text-slate-800">Struktur Tarif Air Resmi</h2>
              <p class="text-sm text-slate-500">Tarif tetap berlaku untuk semua pelanggan warga</p>
            </div>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full border border-emerald-200">
              Aktif
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-sky-50 to-cyan-50/50 p-5 rounded-xl border border-sky-100">
              <div class="text-xs font-semibold uppercase tracking-wider text-sky-700">Tarif Pemakaian</div>
              <div class="mt-2 flex items-baseline gap-1">
                <span class="text-3xl font-extrabold text-sky-900">Rp 5.000</span>
                <span class="text-sm font-medium text-slate-600">/ m³</span>
              </div>
              <p class="mt-2 text-xs text-slate-500">Dihitung murni berdasarkan selisih angka stand meter bulanan.</p>
            </div>

            <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 p-5 rounded-xl border border-slate-200">
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-700">Biaya Abodemen</div>
              <div class="mt-2 flex items-baseline gap-1">
                <span class="text-3xl font-extrabold text-slate-900">Rp 5.000</span>
                <span class="text-sm font-medium text-slate-600">/ bulan</span>
              </div>
              <p class="mt-2 text-xs text-slate-500">Biaya pemeliharaan jaringan pipa dan administrasi meteran.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Keunggulan Section -->
    <section class="py-16 bg-white border-t border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Kemudahan Layanan TirtaPay</h2>
          <p class="mt-3 text-slate-600">Solusi pembayaran digital untuk pengelolaan air lingkungan yang transparan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-md transition">
            <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-xl flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Bayar Online via QRIS & VA</h3>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed">
              Mendukung Midtrans Payment Gateway. Cukup scan kode QRIS menggunakan e-Wallet atau m-Banking favorit Anda.
            </p>
          </div>

          <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-md transition">
            <div class="w-12 h-12 bg-cyan-100 text-cyan-600 rounded-xl flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Riwayat Pemakaian Akurat</h3>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed">
              Pelanggan dapat memantau grafik konsumsi air tiap bulan sehingga pengeluaran lebih terencana dan transparan.
            </p>
          </div>

          <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-md transition">
            <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Kuitansi Digital Resmi</h3>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed">
              Setiap pembayaran yang lunas langsung menghasilkan kuitansi digital sah yang dapat diunduh atau dicetak kapan saja.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="mt-auto py-8 bg-slate-900 text-slate-400 text-center text-sm border-t border-slate-800">
      <div class="max-w-7xl mx-auto px-4">
        <p>&copy; 2026 TirtaPay - Sistem Informasi & Pembayaran Air Bersih. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
</script>
