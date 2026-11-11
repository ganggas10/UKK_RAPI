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

### C. Override / Pemindahan Ruangan (`POST /admin/events/{event}/move`)
Dikelola oleh [`Admin\EventController@moveRoom`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L130-L184):
1. Admin memilih **Ruangan Tujuan Baru (`new_room_id`)** (wajib berbeda dari `room_id` saat ini) dan wajib menuliskan **Alasan Pemindahan (`move_reason`, minimal 10 karakter)**.
2. **Validasi Bentrok di Ruangan Tujuan:** Sistem mengecek apakah ruangan tujuan baru tersebut kosong pada tanggal dan jam pelaksanaan agenda (`Event::overlapping($validated['new_room_id'], ...)`). Jika ruangan baru sudah dipesan kegiatan lain, pemindahan ditolak.
3. **Pembaruan Status & Ruangan:**
   - Mengubah `room_id` dan `moved_to_room_id` ke ruangan baru.
   - Mencatat `moved_by = auth()->id()`, `move_reason`, dan mengubah status menjadi `'moved'`.
4. **Pengiriman Notifikasi Otomatis:** Sistem membuat entri baru di tabel `room_notifications` (`RoomNotification::create`) untuk pemilik agenda (`$event->user_id`) dengan judul `"⚠️ Ruangan Kamu Dipindahkan"` yang memuat nama ruangan asal, nama ruangan baru, tanggal kegiatan, dan alasan pemindahan.

---

## 4. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Kelola Agenda & Pindah Ruang
- **File:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L86-L87)
  - **Baris 86:** `Route::resource('events', AdminEventController::class);` — Rute CRUD agenda Admin (`/admin/events`).
  - **Baris 87:** `Route::post('/events/{event}/move', [AdminEventController::class, 'moveRoom'])->name('events.move');` — Rute eksekusi *override* pindah ruangan.

### B. Controller Agenda Waka / Admin
- **File:** [`app/Http/Controllers/Admin/EventController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php)
  - **[`index()` (Baris 15–74)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L15-L74):** Menampilkan daftar seluruh agenda dengan fitur filter komprehensif: pencarian judul/nama pengaju, status, ruangan, pengaju spesifik, serta rentang tanggal agenda (`withQueryString()`).
  - **[`create()` (Baris 21–26)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L21-L26):** Menampilkan form pembuatan agenda resmi Admin.
  - **[`store()` (Baris 28–77)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L28-L77):** Validasi sesi, pengecekan `Event::overlapping` (Baris 57–68), dan penyimpanan agenda dengan `status = 'approved'` (Baris 71–73).
  - **[`edit()` (Baris 79–84)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L79-L84):** Menampilkan halaman kelola status persetujuan/penolakan dan form pindah ruangan.
  - **[`update()` (Baris 86–117)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L86-L117):** Validasi perubahan status (`pending`, `approved`, `rejected`), wajib isi `rejection_message` & `rejection_notes` jika ditolak (Baris 90–91), serta validasi anti-bentrok saat disetujui (Baris 98–111).
  - **[`destroy()` (Baris 119–125)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L119-L125):** Menghapus data agenda.
  - **[`moveRoom()` (Baris 130–184)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L130-L184):**
    - **Baris 132–138:** Validasi `new_room_id` (`different:room_id`) dan `move_reason` (`min:10`).
    - **Baris 144–155:** Pengecekan bentrok di ruangan tujuan baru (`Event::overlapping`).
    - **Baris 161–167:** Update ruangan, pencatatan `moved_by`, dan perubahan status menjadi `'moved'`.
    - **Baris 172–180:** Pembuatan notifikasi otomatis `RoomNotification::create(...)` untuk Guru/Organisasi pemilik agenda.

### C. Views Terkait
- [`resources/views/admin/events/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/events/index.blade.php) — Tabel daftar seluruh agenda dan tombol aksi.
- [`resources/views/admin/events/create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/events/create.blade.php) — Form tambah agenda resmi oleh Waka/Admin.
- [`resources/views/admin/events/edit.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/events/edit.blade.php) — Form persetujuan/penolakan agenda serta form *Override / Pindah Ruangan*.
