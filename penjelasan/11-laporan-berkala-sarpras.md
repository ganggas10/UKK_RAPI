# Fitur 11: Laporan Berkala Peminjaman & Pengaduan (Harian & Bulanan)

## 1. Deskripsi & Tujuan Fitur
Fitur **Laporan Berkala Sarpras** menyediakan rekapitulasi terpadu mengenai aktivitas **Peminjaman Ruangan (Agenda)** dan **Pengaduan Kerusakan Fasilitas** dalam periode waktu **Harian (`daily`)** maupun **Bulanan (`monthly`)**. Fitur ini membantu Waka Sarpras / Admin dalam menyusun evaluasi rutin keterpakaian ruangan serta kinerja tindak lanjut perbaikan fasilitas sekolah.

---

## 2. Pengguna (Role) yang Terlibat
- **Waka / Admin (`admin`):** Mengakses dan memfilter laporan berkala melalui menu **Laporan** (`/admin/reports`).

---

## 3. Alur Kerja (Workflow) & Perhitungan Rekapitulasi

Dikelola oleh [`Admin\ReportController@index`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/ReportController.php#L15-L42) dan ditampilkan pada [`resources/views/admin/reports/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/reports/index.blade.php):

### A. Penentuan Rentang Tanggal Periode (`$from` & `$to`)
1. **Mode Harian (`?period=daily&date=YYYY-MM-DD` — Default):**
   - `$from` dan `$to` diatur ke tanggal tunggal yang dipilih (`$date->toDateString()`).
2. **Mode Bulanan (`?period=monthly&month=YYYY-MM`):**
   - `$from` diatur ke hari pertama bulan tersebut (`$month->copy()->startOfMonth()->toDateString()`).
   - `$to` diatur ke hari terakhir bulan tersebut (`$month->copy()->endOfMonth()->toDateString()`).

### B. Pengambilan Data & Agregasi Statistik Status
1. **Data Agenda (`$events`):**
   Mengambil seluruh agenda beserta relasi `room` dan `user` yang jadwal pelaksanaannya berada pada rentang `$from` s/d `$to`, kemudian mengelompokkan jumlah per status menggunakan koleksi `$events->countBy('status')` (`$eventStatus`).
2. **Data Pengaduan (`$complaints`):**
   Mengambil seluruh laporan kerusakan beserta relasi `room` dan `reporter` yang tanggal pembuatannya (`DATE(created_at)`) berada di antara `$from` dan `$to`, kemudian mengelompokkan jumlah per status perbaikan menggunakan `$complaints->countBy('status_repair')` (`$complaintStatus`).

---

## 4. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Laporan
- **File:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L67)
  - **Baris 67:** `Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');` (`/admin/reports`)

### B. Controller Laporan Berkala
- **File:** [`app/Http/Controllers/Admin/ReportController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/ReportController.php)
  - **[`index()` (Baris 15–42)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/ReportController.php#L15-L42):**
    - **Baris 17–21:** Validasi input `period` (`daily` atau `monthly`), `date` (`date`), dan `month` (`date_format:Y-m`).
    - **Baris 23–27:** Konversi parameter tanggal menggunakan `Carbon` menjadi batas awal (`$from`) dan batas akhir (`$to`).
    - **Baris 29–32:** Query data peminjaman ruangan (`$events`) yang berlangsung dalam rentang periode.
    - **Baris 33–34:** Query data pengaduan kerusakan (`$complaints`) yang dibuat dalam rentang periode.
    - **Baris 36–41:** Pengiriman data ke view `admin.reports.index` beserta rekapitulasi `$eventStatus` dan `$complaintStatus`.

### C. View Laporan
- **File:** [`resources/views/admin/reports/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/reports/index.blade.php)
  - Menampilkan pemilih periode Harian/Bulanan, kartu ringkasan jumlah agenda & pengaduan per status, serta tabel rincian kegiatan dan laporan kerusakan pada periode terpilih.
