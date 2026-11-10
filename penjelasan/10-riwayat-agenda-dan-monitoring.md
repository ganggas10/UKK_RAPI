# Fitur 10: Monitoring & Arsip Riwayat Agenda (Read-Only Waka/Admin)

## 1. Deskripsi & Tujuan Fitur
Fitur **Riwayat Agenda** adalah pusat arsip dan audit *read-only* bagi pimpinan sekolah (**Waka / Admin**) untuk menelusuri seluruh kegiatan peminjaman ruangan yang pernah diajukan maupun dilaksanakan di sekolah. Berbeda dengan menu *Kelola Agenda* yang berfokus pada tindakan operasional (persetujuan/penolakan), menu **Riwayat Agenda** dirancang khusus untuk pencarian mendalam, pemfilteran multi-kriteria, dan peninjauan jejak histori agenda.

---

## 2. Pengguna (Role) yang Terlibat
- **Waka / Admin (`admin`):** Mengakses daftar arsip dan detail riwayat agenda melalui menu **Riwayat Agenda** (`/admin/agenda-history`).

---

## 3. Alur Kerja (Workflow) & Fitur Pencarian

### A. Filter & Pencarian Multi-Kriteria (`GET /admin/agenda-history`)
Dikelola oleh [`Admin\AgendaHistoryController@index`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/AgendaHistoryController.php#L15-L90):
1. **Pencarian Kata Kunci (`search`):**
   Mencari secara simultan pada **Judul Agenda (`title`)**, **Nama Pemohon (`user.name`)**, maupun **Nama Ruangan (`room.name`)**.
2. **Filter Status (`status`):**
   Memfilter berdasarkan salah satu dari 4 status: `pending`, `approved`, `rejected`, atau `moved`.
3. **Filter Pemohon (`user_id`):**
   Memfilter riwayat pengajuan berdasarkan akun pemohon tertentu dari dropdown daftar pemohon (`$applicants`).
4. **Filter Rentang Tanggal (`date_from` & `date_to`):**
   Menampilkan agenda yang rentang pelaksanaannya beririsan dengan tanggal yang dipilih (`COALESCE(start_date, date) <= $date_to` dan `COALESCE(end_date, date) >= $date_from`).
5. **Pengurutan Dinamis (`sort`):**
   - `submitted_desc` (Default): Berdasarkan waktu pengajuan terbaru (`latest('created_at')`).
   - `submitted_asc`: Berdasarkan waktu pengajuan terlama (`oldest('created_at')`).
   - `date_desc`: Berdasarkan tanggal pelaksanaan terbaru.
   - `date_asc`: Berdasarkan tanggal pelaksanaan terlama.

### B. Halaman Detail Arsip Agenda (`GET /admin/agenda-history/{event}`)
Dikelola oleh [`Admin\AgendaHistoryController@show`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/AgendaHistoryController.php#L92-L102):
- Memuat relasi `room`, `user`, `movedToRoom`, dan `movedByUser` untuk menampilkan rincian lengkap agenda beserta jejak penolakan atau pemindahan ruangannya dalam mode *read-only*.

---

## 4. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Riwayat Agenda
- **File:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L94-L97)
  - **Baris 95:** `Route::get('/agenda-history', [AgendaHistoryController::class, 'index'])->name('waka.agenda.history');`
  - **Baris 96:** `Route::get('/agenda-history/{event}', [AgendaHistoryController::class, 'show'])->name('waka.agenda.detail');`

### B. Controller Riwayat Agenda
- **File:** [`app/Http/Controllers/Admin/AgendaHistoryController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/AgendaHistoryController.php)
  - **[`STATUSES` Constant (Baris 13)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/AgendaHistoryController.php#L13):** Definisi konstanta status `['pending', 'approved', 'rejected', 'moved']`.
  - **[`index()` (Baris 15–90)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/AgendaHistoryController.php#L15-L90):**
    - **Baris 17–26:** Validasi parameter filter URL (`search`, `status`, `date_from`, `date_to`, `user_id`, `sort`).
    - **Baris 33–40:** Query pencarian multi-kolom (`title`, relasi `user.name`, dan relasi `room.name`).
    - **Baris 42–43:** Filter berdasarkan `status` dan `user_id`.
    - **Baris 45–55:** Filter irisan rentang tanggal pelaksanaan (`date_from` s/d `date_to`).
    - **Baris 57–62:** Ekspresi `match` untuk pengurutan data (`submitted_desc`, `submitted_asc`, `date_desc`, `date_asc`).
    - **Baris 65–76:** Perhitungan agregat jumlah status agenda (`$stats`).
    - **Baris 78–81:** Query daftar pemohon (`$applicants`) yang pernah mengajukan agenda.
    - **Baris 83–89:** Merender view `admin.agenda.history`.
  - **[`show()` (Baris 92–102)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/AgendaHistoryController.php#L92-L102):** Eager loading relasi lengkap (`room`, `user`, `movedToRoom`, `movedByUser`) dan merender view `admin.agenda.detail`.

### C. Views Terkait
- [`resources/views/admin/agenda/history.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/agenda/history.blade.php) — Halaman tabel arsip agenda dengan bar pencarian dan filter lengkap.
- [`resources/views/admin/agenda/detail.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/agenda/detail.blade.php) — Halaman rincian lengkap satu riwayat agenda (*read-only*).
