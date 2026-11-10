# Fitur 06: Manajemen Penugasan PIC Ruang & Dukungan Multi-PIC

## 1. Deskripsi & Tujuan Fitur
Fitur **Manajemen Penugasan PIC Ruang** digunakan oleh Waka / Admin untuk mengatur siapa saja petugas **PIC (Person In Charge)** yang bertanggung jawab atas pemeliharaan dan tindak lanjut kerusakan fasilitas di setiap ruangan sekolah.

Fitur ini mendukung **Multi-PIC per Ruangan** (satu ruangan dapat dikelola oleh lebih dari satu PIC secara bersamaan, misalnya `Lab 7`, `Lab 21`, dan `Lab 22`), mencatat **riwayat lengkap pergantian/penghentian PIC** beserta alasannya (`replacement_reason`), serta mengirimkan **notifikasi otomatis (`RoomNotification`)** kepada PIC yang bersangkutan.

---

## 2. Pengguna (Role) yang Terlibat
- **Waka / Admin (`admin`):** Menugaskan PIC baru ke ruangan, mengganti PIC lama dengan PIC baru, menghentikan penugasan PIC, dan meninjau riwayat penugasan per ruangan.
- **PIC Ruang (`pic`):** Menerima penugasan ruangan dan hanya dapat mengelola laporan pengaduan kerusakan pada ruangan yang ditugaskan kepadanya.

---

## 3. Alur Kerja (Workflow) & Logika Bisnis

### A. Daftar Penugasan & Filter Multi-Kriteria (`GET /admin/pic-assignments`)
Dikelola oleh [`Admin\PicAssignmentController@index`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L18-L60):
- Menampilkan daftar ruangan beserta seluruh PIC aktif yang bertugas di ruangan tersebut (`with(['pic', 'activePicAssignments.pic'])`).
- Mendukung pencarian nama ruangan/PIC (`search`), filter status penugasan (`assigned` / `unassigned`), filter berdasarkan nama PIC (`pic_id`), serta filter kategori lokasi (`category`).

### B. Menugaskan PIC ke Ruangan (`POST /admin/pic-assignments`)
Dikelola oleh [`Admin\PicAssignmentController@store`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L70-L104):
1. Menggunakan transaksi database (`DB::transaction`) dan `lockForUpdate()` untuk mencegah *race condition*.
2. Memastikan PIC yang dipilih belum berstatus `aktif` di ruangan yang sama (`abort_if($alreadyAssigned, 422, ...)`).
3. Menyimpan penugasan baru dengan `status = 'aktif'` tanpa menonaktifkan ko-PIC lain yang sudah ada di ruangan tersebut (mendukung **Multi-PIC**).
4. Mengirimkan notifikasi otomatis [`RoomNotification`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/RoomNotification.php) kepada PIC terpilih.

### C. Mengganti PIC Ruangan (`PUT /admin/pic-assignments/{picAssignment}`)
Dikelola oleh [`Admin\PicAssignmentController@update`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L128-L171):
1. Admin memilih PIC pengganti baru (`pic_id`), tanggal mulai (`starts_at`), dan wajib mengisi alasan penggantian (`replacement_reason`).
2. Penugasan PIC lama diubah menjadi `status = 'selesai'`, dicatat `ends_at`, dan disimpan `replacement_reason`-nya.
3. Penugasan PIC baru dibuat dengan `status = 'aktif'`.
4. Sistem mengirimkan notifikasi berakhirnya tugas ke PIC lama dan notifikasi tugas baru ke PIC pengganti.

### D. Menghentikan Penugasan & Melihat Riwayat (`DELETE` & `GET /admin/pic-assignments/history`)
- [`destroy()`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L173-L183): Mengubah status penugasan menjadi `selesai` dan mengisi `ends_at = now()->toDateString()`.
- [`history()`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L185-L190): Menampilkan tabel jejak audit seluruh riwayat penugasan dan pergantian PIC.

---

## 4. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Penugasan PIC
- **File:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L80-L83)
  - **Baris 80:** `Route::get('/pic-assignments/history', [PicAssignmentController::class, 'history'])->name('pic-assignments.history');`
  - **Baris 81:** `Route::get('/pic-assignments/create', [PicAssignmentController::class, 'create'])->name('pic-assignments.create');`
  - **Baris 82:** `Route::get('/pic-assignments/{picAssignment}/edit', [PicAssignmentController::class, 'edit'])->name('pic-assignments.edit');`
  - **Baris 83:** `Route::resource('pic-assignments', PicAssignmentController::class)->except(['create', 'edit']);`

### B. Controller Penugasan PIC
- **File:** [`app/Http/Controllers/Admin/PicAssignmentController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php)
  - **[`index()` (Baris 18–60)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L18-L60):** Filter pencarian, status penugasan, filter PIC, dan statistik ringkasan (`$stats`).
  - **[`create()` (Baris 62–68)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L62-L68):** Menampilkan form tambah penugasan PIC.
  - **[`store()` (Baris 70–104)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L70-L104):** Validasi duplikasi PIC aktif (Baris 82–86), penyimpanan `PicAssignment` baru (Baris 88–92), dan pembuatan `RoomNotification` (Baris 96–100).
  - **[`show()` (Baris 106–116)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L106-L116):** Menampilkan detail penugasan beserta daftar keluhan ruangan dan histori PIC ruangan tersebut.
  - **[`edit()` & `update()` (Baris 118–171)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L118-L171):** Transaksi penggantian PIC lama ke PIC baru beserta pencatatan `replacement_reason` dan pengiriman 2 notifikasi otomatis.
  - **[`destroy()` (Baris 173–183)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L173-L183):** Penghentian penugasan aktif (`status = 'selesai'`).
  - **[`history()` (Baris 185–190)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/PicAssignmentController.php#L185-L190):** Daftar riwayat seluruh penugasan PIC.

### C. Model & Seeder Penugasan Multi-PIC
- **File Model:** [`app/Models/PicAssignment.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/PicAssignment.php#L12-L35)
  - **Baris 12–15:** Atribut `$fillable` (`room_id`, `pic_id`, `starts_at`, `ends_at`, `status`, `notes`, `replacement_reason`, `assigned_by`).
  - **Baris 22–35:** Relasi `room()`, `pic()`, dan `assignedBy()`.
- **File Seeder Penugasan Lab & Ruangan:** [`database/seeders/UserSeeder.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/seeders/UserSeeder.php#L140-L195)
  - Pemetaan otomatis penugasan ruangan untuk **Lega Danu Kumara** (Lab 2, 4, 6, 8, 11, 14, 19, 21, 22), **Wahid Abdurrahman** (Lab 3, 7, 9, 16, 17, 20, 21, 22), **Heri Prasetyo** (Lab 1, 5, 7, 10, 12, 13, 18, 21, 22), dan **Arif Kurniawan** (Ruang Wijaya).

### D. Views & Migration
- **Views:**
  - [`resources/views/admin/pic-assignments/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/pic-assignments/index.blade.php)
  - [`resources/views/admin/pic-assignments/create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/pic-assignments/create.blade.php)
  - [`resources/views/admin/pic-assignments/edit.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/pic-assignments/edit.blade.php)
  - [`resources/views/admin/pic-assignments/show.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/pic-assignments/show.blade.php)
  - [`resources/views/admin/pic-assignments/history.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/pic-assignments/history.blade.php)
- **Migration:** [`database/migrations/2025_01_02_000002_create_pic_assignments_table.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/migrations/2025_01_02_000002_create_pic_assignments_table.php)
