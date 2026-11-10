# Fitur 01: Beranda Utama, Kalender Publik Terintegrasi & Unduh APK Mobile

## 1. Deskripsi & Tujuan Fitur
Fitur ini merupakan halaman utama (*landing page*) dari sistem **RAPI-Sekolah (Rencana Agenda & Penjadwalan Terintegrasi)** yang dapat diakses secara publik tanpa perlu login. Fitur ini dirancang khusus agar **Siswa**, **Tamu**, maupun seluruh warga sekolah dapat:
1. Melihat jadwal penggunaan ruangan dan agenda kegiatan sekolah secara *real-time* dan transparan melalui kalender interaktif (**FullCalendar**).
2. Melihat informasi **Hari Libur Nasional & Hari Penting** yang terintegrasi langsung di dalam kalender.
3. Mengunduh aplikasi mobile Android (**APK RAPI-Sekolah**) secara langsung untuk peran Guru dan Organisasi Siswa.
4. Mengakses pintu masuk (*Login Sistem*) bagi pengguna yang memiliki akun (Waka/Admin, PIC Ruang, Guru, dan Organisasi).

---

## 2. Pengguna (Role) yang Terlibat
- **Publik / Siswa / Tamu (Tanpa Login):** Dapat melihat seluruh kalender agenda yang telah disetujui (`approved`) atau dipindahkan (`moved`), melihat detail kegiatan pada modal interaktif, melihat daftar agenda per tanggal, serta mengunduh file APK.
- **Semua Pengguna Terautentikasi (`admin`, `pic`, `guru`, `organisasi`):** Tetap dapat mengakses halaman kalender publik dan memiliki tombol pintasan menuju Dashboard masing-masing.

---

## 3. Alur Kerja (Workflow) & Logika Bisnis

### A. Penampilan Kalender Agenda & Hari Libur Nasional
1. Saat pengguna membuka halaman utama (`GET /` atau `GET /calendar`), [`CalendarController@index`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php#L19-L49) merender view [`resources/views/calendar/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/calendar/index.blade.php), menghitung statistik publik (`totalRooms`, `totalApprovedEvents`, `totalResolvedComplaints`), serta mengecek ketersediaan file `.apk` di server.
2. Komponen **FullCalendar** di halaman beranda melakukan *fetch* data JSON dari endpoint `GET /calendar/events` ([`CalendarController@getEvents`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php#L118-L223)):
   - Mengambil seluruh agenda dengan status `approved` dan `moved`, lengkap dengan relasi ruangan (`room`) dan pembuat (`user`).
   - Mengambil daftar Hari Libur Nasional & Peringatan penting dari tabel `national_holidays` dan menggabungkannya (`concat`) ke dalam satu respons JSON.
3. **Pewarnaan Otomatis Berdasarkan Peran Pembuat (`Event::creatorColor`):**
   - **Biru (`#0d6efd`):** Agenda resmi yang dibuat oleh **Waka / Admin**.
   - **Hijau (`#198754`):** Agenda yang diajukan oleh **Guru**.
   - **Ungu (`#6f42c1`):** Agenda yang diajukan oleh **Organisasi Siswa**.
   - **Oranye (`#fd7e14`):** Agenda yang dibuat oleh **PIC Ruang**.

### B. Fitur Unduh APK Mobile (`GET /download-apk`)
1. Pengguna menekan tombol **"Unduh APK Mobile"** di bagian Hero atau Navigasi.
2. Method [`CalendarController::resolveApkPath`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php#L54-L95) memindai lokasi file APK prioritas (`public/apk/rapi-sekolah.apk`, `apk/rapi-sekolah.apk`, `public/rapi-sekolah.apk`, dll.) dan memindai otomatis berkas `*.apk` terbaru berdasarkan waktu modifikasi (`filemtime`).
3. Jika file ditemukan, [`CalendarController@downloadApk`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php#L100-L113) langsung mengirimkan respons unduhan (`response()->download()`) dengan header MIME `application/vnd.android.package-archive` dan nama file `RAPI-Sekolah.apk`.
4. Jika file belum tersedia di server, sistem mengarahkan kembali atau menampilkan modal informasi bahwa APK sedang disiapkan dan mengarahkan pengguna untuk masuk melalui Portal Web.

---

## 4. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route
- **File:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L20-L27)
  - **Baris 23:** `Route::get('/', [CalendarController::class, 'index'])->name('calendar.public');` — Rute beranda utama.
  - **Baris 24:** `Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');` — Rute alias kalender publik.
  - **Baris 25:** `Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');` — Endpoint JSON FullCalendar (Agenda + Hari Libur Nasional).
  - **Baris 26:** `Route::get('/calendar/years', [CalendarController::class, 'getYears'])->name('calendar.years');` — Endpoint JSON daftar tahun kalender.
  - **Baris 27:** `Route::get('/download-apk', [CalendarController::class, 'downloadApk'])->name('apk.download');` — Rute unduh langsung file `.apk`.

### B. Controller Utama
- **File:** [`app/Http/Controllers/CalendarController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php)
  - **[`index()` (Baris 19–49)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php#L19-L49):** Menyiapkan data statistik beranda, kategori hari libur, pengecekan status file APK (`$apkFileExists`), dan merender view `calendar.index`.
  - **[`resolveApkPath()` (Baris 54–95)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php#L54-L95):** Logika pencarian lokasi berkas `.apk` di berbagai direktori server (`public/apk`, `base_path`, dll.) serta pemilihan otomatis file `.apk` terbaru (`glob('/*.apk')`).
  - **[`downloadApk()` (Baris 100–113)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php#L100-L113):** Mengirimkan response download `RAPI-Sekolah.apk` dengan header anti-cache (`no-store, no-cache, must-revalidate`).
  - **[`getEvents()` (Baris 118–223)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php#L118-L223):**
    - **Baris 121–177:** Mengambil data `Event` berstatus `approved` dan `moved` serta memformat atribut `extendedProps` untuk FullCalendar.
    - **Baris 180–218:** Mengambil data `NationalHoliday` beserta ikon kategori (`🔴`, `🕌`, `✝️`, `🪔`, `🪷`, `🏮`, `🌸`, `⭐`).
    - **Baris 220–222:** Menggabungkan agenda sekolah dan hari libur nasional (`$schoolEvents->concat($nationalHolidays)`).
  - **[`getYears()` (Baris 228–235)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/CalendarController.php#L228-L235):** Mengambil daftar tahun unik dari tabel `national_holidays`.

### C. Model & Helper Warna Kalender
- **File:** [`app/Models/Event.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Event.php)
  - **[`bookingStartDate()` & `bookingEndDate()` (Baris 46–54)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Event.php#L46-L54):** Menentukan tanggal mulai dan selesai kegiatan.
  - **[`bookingDurationInDays()` (Baris 56–59)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Event.php#L56-L59):** Menghitung durasi hari kegiatan.
  - **[`creatorRole()` & `creatorRoleLabel()` (Baris 81–95)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Event.php#L81-L95):** Mendapatkan role dan label pembuat agenda (`Waka / Admin`, `Guru`, `Organisasi`, `PIC Ruang`).
  - **[`creatorColor()` & `creatorBorderColor()` (Baris 97–117)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Event.php#L97-L117):** Menentukan kode warna HEX kalender berdasarkan role pembuat.

### D. View (Antarmuka Pengguna)
- **File:** [`resources/views/calendar/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/calendar/index.blade.php)
  - **Baris 1–350:** Struktur CSS desain *Landing Page Editorial* RAPI-Sekolah & kustomisasi tampilan FullCalendar.
  - **Baris 352–385:** Navigasi atas (*Navbar*) dengan tautan Beranda, Fitur Sistem, Kalender Agenda, Unduh APK, dan Masuk Sistem / Dashboard.
  - **Baris 387–455:** Bagian *Hero Section* dengan tombol CTA **Login Sistem** & **Unduh APK Mobile** serta kartu pratinjau metrik *real-time*.
  - **Baris 457–510:** Bagian 3 Kartu Fitur Utama Sistem & 2 Kartu Akses Platform (Portal Web vs Aplikasi Mobile APK).
  - **Baris 512–620:** Bagian **Kalender Agenda Terintegrasi** beserta filter tahun, filter kategori, dan legenda warna.
  - **Baris 625–850:** Modal Detail Agenda, Modal Rekap Tanggal, Modal Info APK, dan script inisialisasi **FullCalendar 6**.
