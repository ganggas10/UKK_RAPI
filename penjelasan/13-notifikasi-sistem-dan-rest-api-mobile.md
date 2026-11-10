# Fitur 13: Sistem Notifikasi Ruangan, Integrasi REST API Mobile & Webhook CI/CD

## 1. Deskripsi & Tujuan Fitur
Fitur ini merupakan jembatan komunikasi *real-time* antara pusat kendali Web (**Waka/Admin & PIC Ruang**) dengan pengguna lapangan (**Guru & Organisasi Siswa**), yang terdiri dari:
1. **Sistem Notifikasi Internal (`RoomNotification`):** Memberikan pemberitahuan otomatis kepada pengguna ketika jadwal ruangannya dipindahkan (*override*) ke ruangan lain oleh Admin, atau ketika seorang PIC ditugaskan/digantikan pada suatu ruangan.
2. **REST API Terintegrasi untuk Aplikasi Mobile (`/api/*`):** Menyediakan seluruh endpoint JSON yang dibutuhkan oleh aplikasi Android **RAPI-Sekolah** (khusus role `guru` dan `organisasi` + kalender publik).
3. **Webhook Migrasi & Seeder Otomatis (`/api/deploy-migrate-{secret}`):** Memungkinkan sinkronisasi skema database dan seeder secara otomatis melalui GitHub Actions CI/CD pada *shared hosting* tanpa memerlukan akses SSH.

---

## 2. Sistem Notifikasi (`RoomNotification`)

### A. Pemicu Notifikasi Otomatis di Codebase
Entri pada tabel `room_notifications` ([`app/Models/RoomNotification.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/RoomNotification.php)) dibuat secara otomatis pada lokasi kode berikut:
1. **Pemindahan Ruangan Agenda ([`Admin\EventController@moveRoom` Baris 172–180](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L172-L180)):** Memberitahukan Guru/Organisasi bahwa ruangan agendanya dipindahkan ke ruangan baru beserta alasannya.
2. **Penugasan Baru PIC Ruang ([`Admin\PicAssignmentController@store` Baris 96–100](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L96-L100)):** Memberitahukan PIC Ruang terkait penugasan ruangan baru.
3. **Pergantian PIC Ruang ([`Admin\PicAssignmentController@update` Baris 157–167](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L157-L167)):** Memberitahukan berakhirnya tugas kepada PIC lama dan penugasan baru kepada PIC pengganti.

### B. Penandaan Status Dibaca (`is_read`)
- **Di Web:** [`Guru\DashboardController@markRead`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/DashboardController.php#L28-L34) (`POST /guru/notifications/{notification}/read`).
- **Di Aplikasi Mobile:** [`Api\NotificationApiController@markRead`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/NotificationApiController.php#L42-L55) (`POST /api/notifications/{notification}/read`) dan [`markAllRead`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/NotificationApiController.php#L60-L72) (`POST /api/notifications/read-all`).

---

## 3. Posisi Source Code Lengkap REST API Mobile & Webhook

### A. Definisi Route API & Webhook Deployment
- **File:** [`routes/api.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/api.php)
  - **Baris 53–62:** Rute autentikasi mobile (`/api/login`, `/api/forgot-password`, `/api/verify-otp`, `/api/reset-password`).
  - **Baris 65–72:** Rute publik mobile tanpa login (`GET /api/events`, `GET /api/events/{event}`, `GET /api/rooms/available`, `GET /api/rooms`).
  - **Baris 75–118:** Closure `Route::get('deploy-migrate-{secret}', ...)` — Menjalankan `Artisan::call('migrate')`, `db:seed` (jika `?seed=1`), `storage:link`, dan `optimize:clear` secara aman menggunakan token rahasia.
  - **Baris 123–142:** Grup rute terautentikasi `Route::middleware('auth:sanctum')` untuk `/api/logout`, `/api/me`, `/api/my-events`, `POST /api/events`, `/api/complaints`, dan `/api/notifications`.

### B. Controller API Event / Kalender & Peminjaman Ruangan
- **File:** [`app/Http/Controllers/Api/EventApiController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php)
  - **[`index()` (Baris 20–50)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L20-L50):** Mengambil daftar agenda publik (`approved` & `moved`) dengan dukungan filter `?month=YYYY-MM` dan `?date=YYYY-MM-DD`.
  - **[`myEvents()` (Baris 56–76)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L56-L76):** Mengambil riwayat pengajuan agenda milik akun Guru/Organisasi yang sedang login.
  - **[`show()` (Baris 82–95)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L82-L95):** Menampilkan detail lengkap satu agenda.
  - **[`store()` (Baris 101–170)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L101-L170):** Mengajukan peminjaman ruangan baru dengan validasi Sesi 1–11 dan pencegahan bentrok jadwal (`HTTP 409 Conflict`).
  - **[`checkAvailability()` (Baris 198–230)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L198-L230):** Mengecek status ketersediaan seluruh ruangan pada tanggal dan jam tertentu.
  - **[`formatEvent()` (Baris 235–273)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L235-L273):** Helper pemformatan struktur JSON agenda.

### C. Controller API Pengaduan Kerusakan
- **File:** [`app/Http/Controllers/Api/ComplaintApiController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php)
  - **[`index()` (Baris 17–37)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php#L17-L37):** Mengambil daftar laporan kerusakan milik pengguna yang sedang login.
  - **[`show()` (Baris 43–52)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php#L43-L52):** Menampilkan detail laporan kerusakan beserta catatan PIC (`pic_notes`).
  - **[`store()` (Baris 63–101)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php#L63-L101):** Mengirim laporan kerusakan baru beserta unggahan foto bukti (`multipart/form-data`, maks. 5MB).
  - **[`rooms()` (Baris 107–112)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php#L107-L112):** Mengembalikan daftar seluruh ruangan (`id`, `name`, `location`, `capacity`).
  - **[`formatComplaint()` (Baris 117–147)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php#L117-L147):** Helper pemformatan JSON pengaduan beserta pembuatan URL publik foto (`Storage::url($complaint->photo_path)`).

### D. Controller API Notifikasi
- **File:** [`app/Http/Controllers/Api/NotificationApiController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/NotificationApiController.php)
  - **[`index()` (Baris 15–36)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/NotificationApiController.php#L15-L36):** Mengambil daftar notifikasi pengguna beserta penghitungan `unread_count`.
  - **[`markRead()` (Baris 42–55)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/NotificationApiController.php#L42-L55):** Menandai satu notifikasi sebagai sudah dibaca (`is_read = true`).
  - **[`markAllRead()` (Baris 60–72)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/NotificationApiController.php#L60-L72):** Menandai seluruh notifikasi milik pengguna sebagai sudah dibaca.
  - **[`formatNotification()` (Baris 77–87)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/NotificationApiController.php#L77-L87):** Helper pemformatan JSON notifikasi.

### E. Model, Migration & Workflow CI/CD
- **Model Notifikasi:** [`app/Models/RoomNotification.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/RoomNotification.php#L1-L25)
- **Migration Notifikasi:** [`database/migrations/2025_01_02_000005_create_room_notifications_table.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/migrations/2025_01_02_000005_create_room_notifications_table.php)
- **Workflow GitHub Actions CI/CD:** [`.github/workflows/deploy.yml`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/.github/workflows/deploy.yml)
