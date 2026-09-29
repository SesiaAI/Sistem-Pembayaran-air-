# TirtaPay - Sistem Informasi & Pembayaran Air Bersih

Aplikasi modern pengelolaan air bersih dan pembayaran tagihan bulanan berbasis **Laravel 12 (REST API)**, **Vue 3 (Vite + Tailwind CSS)**, dan **Midtrans Payment Gateway (QRIS & Virtual Account)**.

---

## 🚀 Fitur Utama

1. **Role & Hak Akses:**
   * **Super Admin**: Manajemen seluruh data, kontrol tarif air & abodemen, kelola akun petugas/admin, dan laporan eksekutif.
   * **Admin Lapangan**: Input stand meter bulanan warga via HP/tablet, kasir penerimaan pembayaran tunai di loket, dan kelola pelanggan.
   * **Pelanggan**: Pendaftaran akun menggunakan No. HP & Nama, pantau riwayat pemakaian air (m³), cek tagihan, bayar via Midtrans (QRIS / VA) atau simulasi instan, dan cetak kuitansi resmi.

2. **Aturan Bisnis & Tarif Air:**
   * **Tarif Pemakaian**: Rp 5.000 per 1 $m^3$ (dihitung otomatis dari selisih stand meter akhir - stand awal).
   * **Biaya Abodemen**: Rp 5.000 per bulan (biaya beban tetap pemeliharaan).
   * **Rumus Total**: $(\text{Pemakaian } m^3 \times 5.000) + 5.000$.

---

## 🔑 Akun Login Default (Testing & Demo)

Semua akun default menggunakan password: `password`

| Role | Nomor HP Login | Kata Sandi | Deskripsi |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `081234567890` | `password` | Akses penuh & pengaturan tarif |
| **Admin Lapangan** | `081234567891` | `password` | Pencatatan meter & kasir loket |
| **Pelanggan** | `081234567892` | `password` | Budi Santoso (ada tagihan aktif & riwayat lunas) |
| **Pelanggan 2** | `081234567893` | `password` | Siti Rahayu |

*(Di halaman login juga telah disediakan tombol **1-Klik Coba Cepat** sehingga tidak perlu mengetik manual)*

---

## 💻 Cara Menjalankan Aplikasi

### Cara 1: Sekali Klik (Rekomendasi di Windows)
Cukup klik dua kali file:
```text
start-dev.bat
```
Script ini akan otomatis menjalankan Backend di port `8000` dan Frontend di port `5173`.

---

### Cara 2: Menjalankan Secara Manual

**1. Jalankan Backend (Laravel 12):**
```bash
cd backend
php artisan serve --port=8000
```
*(Backend berjalan di `http://127.0.0.1:8000`)*

**2. Jalankan Frontend (Vue 3):**
```bash
cd frontend
npm run dev
```
*(Buka browser di `http://localhost:5173`)*

---

## 🗄️ Konfigurasi PostgreSQL (Saat Siap Digunakan)

Saat ini backend menggunakan SQLite untuk kemudahan pengujian langsung tanpa hambatan. Ketika Anda siap beralih ke database **PostgreSQL** lokal atau Cloud (seperti Supabase):

1. Buka file [backend/.env](file:///d:/Kuliah/Semester%208/Sistem%20Pembayaran%20air/backend/.env)
2. Ubah bagian koneksi database:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=pembayaran_air
   DB_USERNAME=postgres
   DB_PASSWORD=password_anda
   ```
3. Jalankan migrasi dan seeder ulang:
   ```bash
   cd backend
   php artisan migrate:fresh --seed
   ```

---

## 💳 Konfigurasi Midtrans Payment Gateway

1. Daftar akun di [Midtrans Sandbox](https://dashboard.sandbox.midtrans.com/).
2. Buka menu **Settings > Access Keys**, lalu salin kredensial Anda ke file [backend/.env](file:///d:/Kuliah/Semester%208/Sistem%20Pembayaran%20air/backend/.env):
   ```env
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
   MIDTRANS_MERCHANT_ID=Gxxxxxxxxx
   MIDTRANS_IS_PRODUCTION=false
   ```
3. Di dashboard Midtrans, atur **Payment Notification URL** ke:
   ```text
   http://domain-anda.com/api/payment/webhook
   ```
   *(Untuk testing lokal bisa menggunakan tunneling ngrok)*
4. Jika belum memasang kunci Midtrans, sistem tetap menyediakan **Tombol Simulasi Bayar Instan** di modal pembayaran sehingga proses demo dan pengujian tetap berjalan 100% mulus!
