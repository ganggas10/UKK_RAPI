# Fitur 08: Persetujuan Agenda & Override Pindah Ruangan oleh Waka/Admin

## 1. Deskripsi & Tujuan Fitur
Fitur ini memberikan wewenang penuh kepada **Waka / Admin** untuk mengelola seluruh jadwal kegiatan sekolah, yang mencakup:
1. **Membuat Agenda Resmi Sekolah** yang langsung berstatus *Disetujui (`approved`)* dan tampil di Kalender Publik.
2. **Menyetujui (`approved`) atau Menolak (`rejected`)** permohonan peminjaman ruangan yang diajukan oleh Guru dan Organisasi Siswa.
3. **Memindahkan Ruangan (*Override / Move Room*):** Memindahkan jadwal kegiatan Guru/Organisasi ke ruangan lain apabila ruangan awal dibutuhkan untuk agenda prioritas sekolah, lengkap dengan **notifikasi otomatis (`RoomNotification`)** kepada pemilik agenda.

---

## 2. Pengguna (Role) yang Terlibat
- **Waka / Admin (`admin`):** Mengelola persetujuan, penolakan, pembuatan agenda resmi, dan pemindahan ruangan melalui menu **Kelola Agenda** (`/admin/events`).
- **Guru & Organisasi (`guru`, `organisasi`):** Menerima hasil keputusan persetujuan/penolakan serta menerima notifikasi `RoomNotification` apabila jadwal ruangannya dipindahkan oleh Admin.

---

## 3. Alur Kerja (Workflow) & Logika Bisnis

### A. Pembuatan Agenda Resmi oleh Waka/Admin (`POST /admin/events`)
Dikelola oleh [`Admin\EventController@store`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L28-L77):
1. Admin memilih ruangan, mengisi judul agenda, rentang tanggal, serta sesi mulai & selesai (`Sesi 1`–`Sesi 11`).
2. Sistem menjalankan pengecekan anti-bentrok (`Event::overlapping()`) agar Admin tidak menumpuk jadwal di ruangan yang sudah terpakai.
3. Agenda yang dibuat oleh Admin langsung disimpan dengan `status = 'approved'`, sehingga seketika tampil di Kalender Publik.

### B. Persetujuan & Penolakan Pengajuan Agenda (`PUT /admin/events/{event}`)
Dikelola oleh [`Admin\EventController@update`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L86-L117):
1. Admin memilih status (`pending`, `approved`, atau `rejected`).
2. **Jika Disetujui (`approved`):** Sistem kembali memvalidasi `Event::overlapping(..., $event->id)` untuk memastikan tidak ada jadwal lain yang bentrok sebelum menyetujui.
3. **Jika Ditolak (`rejected`):** Admin wajib mengisi pesan singkat penolakan (`rejection_message`) dan catatan detail penolakan (`rejection_notes`).

### C. Override / Pemindahan Ruangan & Notifikasi WhatsApp (`POST /admin/events/{event}/move`)
Dikelola oleh [`Admin\EventController@moveRoom`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L205-L270):
1. Admin memilih **Ruangan Tujuan Baru (`new_room_id`)** (wajib berbeda dari `room_id` saat ini) dan wajib menuliskan **Alasan Pemindahan (`move_reason`, minimal 10 karakter)**.
2. **Validasi Bentrok di Ruangan Tujuan:** Sistem mengecek apakah ruangan tujuan baru tersebut kosong pada tanggal dan jam pelaksanaan agenda (`Event::overlapping($validated['new_room_id'], ...)`). Jika ruangan baru sudah dipesan kegiatan lain, pemindahan ditolak.
3. **Pembaruan Status & Ruangan:**
   - Mengubah `room_id` dan `moved_to_room_id` ke ruangan baru.
   - Mencatat `moved_by = auth()->id()`, `move_reason`, dan mengubah status menjadi `'moved'`.
4. **Pengiriman Notifikasi Otomatis:** Sistem membuat entri baru di tabel `room_notifications` (`RoomNotification::create`) untuk pemilik agenda (`$event->user_id`).
5. **Pencatatan Riwayat & Konfirmasi WhatsApp:**
   - Sistem mencatat riwayat pemindahan pada tabel `room_move_histories` dengan status `belum_dikirim`.
   - Mengarahkan Admin kembali ke halaman agenda dengan memunculkan **Modal Konfirmasi Pengiriman WhatsApp**.
   - Admin dapat meninjau ringkasan pemindahan serta pratinjau pesan otomatis sebelum menekan tombol **Kirim WhatsApp** (`wa.me`) atau **Batal**.

---

## 4. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Kelola Agenda, Pindah Ruang & WhatsApp
- **File:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L90-L96)
  - `Route::get('/events/move-history', ...)` — Halaman tabel riwayat pemindahan ruangan.
  - `Route::get('/events/move-history/{moveHistory}/whatsapp', ...)` — Eksekusi pengiriman pesan WhatsApp ke pemilik agenda.
  - `Route::post('/events/move-history/{moveHistory}/cancel-whatsapp', ...)` — Pembatalan pengiriman WhatsApp.
  - `Route::resource('events', AdminEventController::class);` — Rute CRUD agenda Admin (`/admin/events`).
  - `Route::post('/events/{event}/move', [AdminEventController::class, 'moveRoom'])->name('events.move');` — Rute eksekusi *override* pindah ruangan.

### B. Controller Agenda Waka / Admin
- **File:** [`app/Http/Controllers/Admin/EventController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php)
  - **[`index()` (Baris 16–76)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L16-L76):** Menampilkan daftar agenda dengan filter dan memicu modal konfirmasi WhatsApp jika ada pemindahan baru.
  - **[`moveRoom()` (Baris 205–269)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L205-L269):** Validasi bentrok ruangan baru, mutasi data event, pembuatan notifikasi in-app, pencatatan `RoomMoveHistory`, dan trigger modal konfirmasi WhatsApp.
  - **[`sendMoveWhatsApp()` (Baris 271–290)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L271-L290):** Normalisasi nomor WhatsApp penerima (format internasional `628...`), pembaruan status `berhasil_dikirim` / `wa_sent_at`, dan pengalihan ke API `https://wa.me`.
  - **[`cancelMoveWhatsApp()` (Baris 292–296)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L292-L296):** Pembatalan pengiriman WhatsApp dengan tetap mempertahankan perubahan ruangan.
  - **[`moveHistory()` (Baris 298–318)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L298-L318):** Menampilkan halaman riwayat pemindahan ruangan lengkap dengan filter dan tombol kirim ulang.

### C. Model & Migrasi
- **Model:** [`app/Models/RoomMoveHistory.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/RoomMoveHistory.php) — Helper format nomor WhatsApp dan generator template pesan.
- **Migrasi:** [`database/migrations/2026_11_11_143701_create_room_move_histories_table.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/migrations/2026_11_11_143701_create_room_move_histories_table.php) — Tabel `room_move_histories`.

### D. Views Terkait
- [`resources/views/admin/events/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/events/index.blade.php) — Tabel agenda, form modal pindah ruangan, dan modal konfirmasi pengiriman WhatsApp.
- [`resources/views/admin/events/move_history.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/events/move_history.blade.php) — Halaman pemantauan riwayat pemindahan ruangan sekolah dan tombol kirim ulang WhatsApp.
