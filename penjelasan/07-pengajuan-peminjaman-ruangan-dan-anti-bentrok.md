# Fitur 07: Pengajuan Peminjaman Ruangan & Pencegahan Bentrok Jadwal (Anti-Bentrok)

## 1. Deskripsi & Tujuan Fitur
Fitur ini memungkinkan **Guru** dan **Organisasi Siswa** mengajukan permohonan peminjaman ruangan untuk kegiatan belajar-mengajar, rapat, ujian, maupun kegiatan ekstrakurikuler.

Keunggulan utama fitur ini adalah:
1. **Sistem Penjadwalan Berbasis Sesi Jam Pelajaran (`Sesi 1` s/d `Sesi 11`):** Memudahkan pemilihan waktu tanpa perlu mengetik menit secara manual, serta mendukung kegiatan 1 hari (*single-day*) maupun rentang beberapa hari (*multi-day*).
2. **Algoritma Pencegahan Bentrok Jadwal (*Conflict Prevention*):** Menolak secara otomatis di sisi server apabila ruangan yang dipilih sudah dipesan oleh kegiatan lain pada irisan tanggal dan jam yang sama.

---

## 2. Pengguna (Role) yang Terlibat
- **Guru & Organisasi (`guru`, `organisasi`):** Mengajukan peminjaman ruangan baru serta melihat daftar & detail pengajuan miliknya (baik melalui Portal Web `/guru/events` maupun Aplikasi Mobile `/api/events`).
- **Waka / Admin (`admin`):** Meninjau dan menyetujui/menolak pengajuan yang masuk.

---

## 3. Pemetaan Jadwal Sesi Jam Sekolah (`sessionTimes`)
Waktu peminjaman dipetakan ke dalam **11 Sesi Jam Pelajaran** (masing-masing berdurasi 45 menit dari pukul `07:15` hingga `15:30` WIB):

| Sesi | Jam Mulai (`start_time`) | Jam Selesai (`end_time`) |
| :---: | :---: | :---: |
| **Sesi 1** | 07:15 | 08:00 |
| **Sesi 2** | 08:00 | 08:45 |
| **Sesi 3** | 08:45 | 09:30 |
| **Sesi 4** | 09:30 | 10:15 |
| **Sesi 5** | 10:15 | 11:00 |
| **Sesi 6** | 11:00 | 11:45 |
| **Sesi 7** | 11:45 | 12:30 |
| **Sesi 8** | 12:30 | 13:15 |
| **Sesi 9** | 13:15 | 14:00 |
| **Sesi 10** | 14:00 | 14:45 |
| **Sesi 11** | 14:45 | 15:30 |

---

## 4. Alur Kerja (Workflow) & Logika Anti-Bentrok

### A. Pengisian Form & Konversi Sesi ke Waktu
1. Pemohon memilih **Ruangan (`room_id`)**, **Judul Agenda (`title`)**, **Tanggal Mulai (`start_date`)**, **Tanggal Selesai (`end_date`)**, **Sesi Mulai (`start_session`)**, dan **Sesi Selesai (`end_session`)**.
2. Sistem memvalidasi bahwa nomor sesi selesai tidak boleh lebih kecil dari nomor sesi mulai (`sessionNumber($end) < sessionNumber($start)`).
3. Sistem mengambil jam mulai dari `start_session` dan jam selesai dari `end_session`, serta menyimpan label gabungan sesi pada kolom `session` (contoh: `"Sesi 1 - Sesi 3"`).

### B. Pemeriksaan Bentrok Jadwal (`Event::scopeOverlapping`)
Sebelum menyimpan ke database, controller memanggil [`Event::overlapping($roomId, $startDate, $endDate, $startTime, $endTime)`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Event.php#L27-L44):
1. Mengecek seluruh agenda di ruangan yang sama (`room_id`) yang berstatus **`pending`**, **`approved`**, atau **`moved`**.
2. Memeriksa irisan rentang tanggal (`COALESCE(start_date, date) <= $endDate` DAN `COALESCE(end_date, date) >= $startDate`) serta irisan rentang jam (`start_time < $endTime` DAN `end_time > $startTime`).
3. Jika ditemukan jadwal yang beririsan (`$conflict`):
   - Di Web: Mengembalikan redirect dengan flash message `conflict_error` yang menyebutkan nama agenda yang bentrok beserta tanggal dan jamnya.
   - Di API Mobile: Mengembalikan respons JSON dengan kode status **HTTP `409 Conflict`** beserta detail jadwal yang bentrok.
4. Jika tidak ada bentrok, pengajuan disimpan dengan status awal **`pending` (Menunggu Persetujuan)**.

---

## 5. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Pengajuan Agenda (Web & API)
- **File Route Web:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L117)
  - **Baris 117:** `Route::resource('events', GuruEventController::class)->only(['index', 'create', 'store', 'show']);` (`/guru/events`)
- **File Route API:** [`routes/api.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/api.php)
  - **Baris 69:** `Route::get('/rooms/available', [EventApiController::class, 'checkAvailability'])`
  - **Baris 130:** `Route::get('/my-events', [EventApiController::class, 'myEvents'])`
  - **Baris 131:** `Route::post('/events', [EventApiController::class, 'store'])`

### B. Model & Query Scope Anti-Bentrok
- **File:** [`app/Models/Event.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Event.php)
  - **[`scopeOverlapping()` (Baris 27–44)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Event.php#L27-L44):** Query utama pendeteksi tabrakan jadwal ruangan pada tanggal dan sesi jam yang beririsan.

### C. Controller Web (Guru & Organisasi)
- **File:** [`app/Http/Controllers/Guru/EventController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/EventController.php)
  - **[`index()` (Baris 13–21)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/EventController.php#L13-L21):** Menampilkan daftar pengajuan milik user yang sedang login (`where('user_id', auth()->id())`).
  - **[`create()` (Baris 23–28)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/EventController.php#L23-L28):** Menampilkan form pengajuan peminjaman ruangan.
  - **[`store()` (Baris 30–92)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/EventController.php#L30-L92):**
    - **Baris 32–59:** Validasi form dan konversi `start_session` & `end_session` ke `start_time` & `end_time`.
    - **Baris 66–82:** Pengecekan bentrok jadwal menggunakan `Event::overlapping(...)` dan pengembalian pesan `conflict_error`.
    - **Baris 85–88:** Penyimpanan pengajuan baru dengan `status = 'pending'`.
  - **[`show()` (Baris 94–101)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/EventController.php#L94-L101):** Menampilkan detail pengajuan (dilindungi `abort_if($event->user_id !== auth()->id(), 403)`).
  - **[`sessionTimes()` & `sessionNumber()` (Baris 103–123)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/EventController.php#L103-L123):** Konversi nama Sesi 1–11 ke rentang jam `['07:15', '08:00']` s/d `['14:45', '15:30']`.

### D. Controller API Mobile (Guru & Organisasi)
- **File:** [`app/Http/Controllers/Api/EventApiController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php)
  - **[`myEvents()` (Baris 56–76)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L56-L76):** Mengambil daftar pengajuan milik pengguna mobile.
  - **[`store()` (Baris 101–170)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L101-L170):** Validasi, pengecekan `Event::overlapping` (mengembalikan HTTP `409` pada Baris 147–158), dan penyimpanan agenda via API.
  - **[`checkAvailability()` (Baris 198–230)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L198-L230):** Mengecek status ketersediaan (`available: true/false`) untuk seluruh 65 ruangan pada tanggal dan jam tertentu.

### E. Views & Migration
- **Views:**
  - [`resources/views/guru/events/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/guru/events/index.blade.php)
  - [`resources/views/guru/events/create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/guru/events/create.blade.php)
  - [`resources/views/guru/events/show.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/guru/events/show.blade.php)
- **Migration:** [`database/migrations/2025_01_02_000003_create_events_table.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/migrations/2025_01_02_000003_create_events_table.php)
