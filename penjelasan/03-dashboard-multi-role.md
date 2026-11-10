# Fitur 03: Dashboard Multi-Role (Waka/Admin, PIC Ruang, Guru & Organisasi)

## 1. Deskripsi & Tujuan Fitur
Setiap peran pengguna di dalam **RAPI-Sekolah** memiliki halaman **Dashboard (Ikhtisar)** tersendiri yang dirancang sesuai dengan tugas dan tanggung jawab masing-masing. Dashboard menyajikan kartu metrik statistik *real-time*, daftar kegiatan/pengaduan terbaru, notifikasi sistem, serta tombol aksi cepat (*quick actions*).

---

## 2. Pembagian Dashboard & Alur Kerja Berdasarkan Peran

### A. Dashboard Waka / Admin (`GET /admin/dashboard`)
1. **Metrik Statistik Utama (`$stats`):**
   - `total_rooms`: Jumlah seluruh ruangan dan laboratorium di sekolah (`Room::count()`).
   - `total_users`: Jumlah akun pengguna terdaftar (`User::count()`).
   - `pending_events`: Jumlah pengajuan agenda yang menunggu persetujuan (`status = 'pending'`).
   - `open_complaints`: Jumlah pengaduan kerusakan yang berstatus `'Belum Diperbaiki'`.
2. **Daftar Monitoring Cepat:**
   - **5 Agenda Terbaru (`$recent_events`)** beserta informasi ruangan, pemohon, dan statusnya.
   - **5 Pengaduan Belum Diperbaiki (`$open_complaints`)** lengkap dengan fitur tombol **Kirim Peringatan WhatsApp ke PIC Ruang** (`GET /admin/complaints/{complaint}/whatsapp`).

### B. Dashboard PIC Ruang (`GET /pic/dashboard`)
1. **Validasi Cakupan Ruangan (Termasuk Multi-PIC):**
   Mengambil seluruh ruangan yang dikelola oleh PIC yang sedang login, baik sebagai PIC utama (`pic_id`) maupun ko-PIC aktif di tabel `pic_assignments` (`activePicAssignments`).
2. **Metrik Pengaduan Ruangan PIC (`$stats`):**
   - `total_rooms`: Jumlah ruangan yang menjadi tanggung jawab PIC tersebut.
   - `belum_diperbaiki`: Jumlah laporan kerusakan berstatus `'Belum Diperbaiki'` di ruangan PIC.
   - `menunggu_sparepart`: Jumlah laporan kerusakan berstatus `'Menunggu Spare Part'` di ruangan PIC.
   - `sudah_diperbaiki`: Jumlah laporan kerusakan berstatus `'Sudah Diperbaiki'` di ruangan PIC.
3. **Daftar Ruangan & 5 Pengaduan Terbaru (`$myRooms` & `$recent_complaints`):**
   Menampilkan kartu ruangan yang dikelola beserta jumlah keluhan aktif di tiap ruangan, diikuti tabel 5 pengaduan terbaru untuk segera ditindaklanjuti.

### C. Dashboard Guru & Organisasi (`GET /guru/dashboard`)
1. **Banner Notifikasi Pemindahan Ruangan (`$notifications`):**
   Menampilkan peringatan belum dibaca (`is_read = false`) apabila ada jadwal ruangan milik Guru/Organisasi yang dipindahkan (*override*) oleh Waka/Admin ke ruangan lain, lengkap dengan tombol **"Tandai Dibaca"** (`POST /guru/notifications/{notification}/read`).
2. **5 Pengajuan Agenda Terakhir (`$myEvents`):**
   Menampilkan daftar 5 peminjaman ruangan terbaru yang diajukan oleh akun tersebut beserta status persetujuannya.

---

## 3. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Dashboard
- **File:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php)
  - **Baris 40–47:** Route `/dashboard` (pengarah otomatis berdasarkan role).
  - **Baris 63:** `Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');` (`/admin/dashboard`)
  - **Baris 103:** `Route::get('/dashboard', [PicDashboard::class, 'index'])->name('dashboard');` (`/pic/dashboard`)
  - **Baris 114:** `Route::get('/dashboard', [GuruDashboard::class, 'index'])->name('dashboard');` (`/guru/dashboard`)
  - **Baris 123:** `Route::post('/notifications/{notification}/read', [GuruDashboard::class, 'markRead'])->name('notifications.read');`

### B. Controller Dashboard Waka / Admin
- **File:** [`app/Http/Controllers/Admin/DashboardController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/DashboardController.php)
  - **[`index()` (Baris 16–35)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/DashboardController.php#L16-L35):**
    - **Baris 18–23:** Perhitungan array `$stats` (`total_rooms`, `total_users`, `pending_events`, `open_complaints`).
    - **Baris 25:** Query 5 agenda terbaru (`$recent_events`).
    - **Baris 26–32:** Query 5 pengaduan `'Belum Diperbaiki'` beserta relasi `room.pic` dan `reporter`.
    - **Baris 34:** Merender view `admin.dashboard`.

### C. Controller Dashboard PIC Ruang
- **File:** [`app/Http/Controllers/Pic/DashboardController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Pic/DashboardController.php)
  - **[`index()` (Baris 11–43)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Pic/DashboardController.php#L11-L43):**
    - **Baris 16–23:** Query `$myRooms` yang mengambil ruangan berdasarkan `pic_id` atau relasi `activePicAssignments` beserta penghitungan jumlah keluhan (`withCount`).
    - **Baris 26–31:** Perhitungan array `$stats` untuk 3 status perbaikan di ruangan milik PIC.
    - **Baris 33–40:** Query 5 pengaduan terbaru di ruangan milik PIC (`$recent_complaints`).
    - **Baris 42:** Merender view `pic.dashboard`.

### D. Controller Dashboard Guru & Organisasi
- **File:** [`app/Http/Controllers/Guru/DashboardController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/DashboardController.php)
  - **[`index()` (Baris 11–26)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/DashboardController.php#L11-L26):**
    - **Baris 13–17:** Query 5 pengajuan agenda milik pengguna yang sedang login (`$myEvents`).
    - **Baris 20–23:** Query notifikasi pemindahan ruang yang belum dibaca (`$notifications`).
    - **Baris 25:** Merender view `guru.dashboard`.
  - **[`markRead()` (Baris 28–34)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/DashboardController.php#L28-L34):** Memvalidasi kepemilikan notifikasi (`abort_if($notification->user_id !== auth()->id(), 403)`) dan menandai `is_read = true`.

### E. Views & Layout Utama
- **Layout Shell Utama (Sidebar Navy + Kanvas Putih):** [`resources/views/layouts/app.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/layouts/app.blade.php)
- **Stylesheet Sistem Desain:** [`resources/css/app.css`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/css/app.css)
- **View Dashboard Admin:** [`resources/views/admin/dashboard.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/dashboard.blade.php)
- **View Dashboard PIC:** [`resources/views/pic/dashboard.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/pic/dashboard.blade.php)
- **View Dashboard Guru/Organisasi:** [`resources/views/guru/dashboard.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/guru/dashboard.blade.php)
