# Fitur 04: Manajemen Data Ruangan Sekolah

## 1. Deskripsi & Tujuan Fitur
Fitur **Manajemen Ruangan** berfungsi untuk mengelola seluruh data master ruangan dan fasilitas yang tersedia di sekolah. Data ruangan ini menjadi acuan utama bagi fitur **Peminjaman Ruangan (Agenda)**, **Penugasan PIC Ruang**, dan **Pengaduan Kerusakan Fasilitas**.

Secara bawaan (*default*), sistem telah dikonfigurasi dengan **65 ruangan standar sekolah** yang mencakup:
- **Ruang Kelas:** Ruang 1 s/d Ruang 35
- **Laboratorium Komputer:** Lab 1 s/d Lab 22
- **Laboratorium Khusus:** Laboratorium IPA & Laboratorium Penataan Barang
- **Fasilitas Umum & Ibadah:** Masjid, Ruang Wijaya, Lapangan, Aula Atas, dan Aula Bawah.

---

## 2. Pengguna (Role) yang Terlibat
- **Waka / Admin (`admin`):** Memiliki akses penuh (*Create, Read, Update, Delete* / CRUD) untuk menambah, mengubah, melihat, dan menghapus data ruangan melalui menu **Kelola Ruangan** (`/admin/rooms`).
- **Guru & Organisasi (`guru`, `organisasi`):** Melihat daftar ruangan saat mengajukan peminjaman ruangan atau membuat laporan pengaduan kerusakan.
- **PIC Ruang (`pic`):** Melihat ruangan yang ditugaskan kepadanya.

---

## 3. Alur Kerja (Workflow) & Logika Bisnis

### A. Daftar Ruangan & Informasi Multi-PIC (`GET /admin/rooms`)
1. Method [`Admin\RoomController@index`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L11-L16) mengambil data ruangan secara berurutan (`orderBy('id')`) beserta relasi `pic` dan `activePicAssignments.pic` dengan paginasi 10 ruangan per halaman.
2. Halaman [`resources/views/admin/rooms/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/rooms/index.blade.php) menampilkan nama ruangan, lokasi, kapasitas, serta lencana (*badge*) seluruh nama PIC yang sedang aktif bertugas di ruangan tersebut.

### B. Menambah Ruangan Baru (`GET /admin/rooms/create` & `POST /admin/rooms`)
1. Admin mengisi formulir pada [`resources/views/admin/rooms/create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/rooms/create.blade.php).
2. Method [`Admin\RoomController@store`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L23-L35) memvalidasi:
   - `name`: Wajib diisi, string maksimal 100 karakter.
   - `location`: Opsional, string maksimal 150 karakter.
   - `capacity`: Wajib diisi, angka bulat minimal 1.

### C. Mengubah & Menghapus Ruangan (`PUT /admin/rooms/{room}` & `DELETE /admin/rooms/{room}`)
1. Method [`Admin\RoomController@update`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L42-L54) memvalidasi perubahan nama, lokasi, dan kapasitas ruangan lalu menyimpannya ke database.
2. Method [`Admin\RoomController@destroy`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L56-L62) menghapus data ruangan dari database.

---

## 4. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Ruangan
- **File Route Web:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L73)
  - **Baris 73:** `Route::resource('rooms', RoomController::class);` — Menghasilkan rute CRUD lengkap (`/admin/rooms`, `/admin/rooms/create`, `/admin/rooms/{room}/edit`, dll.).
- **File Route API:** [`routes/api.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/api.php#L69-L72)
  - **Baris 69:** `Route::get('/rooms/available', [EventApiController::class, 'checkAvailability'])` — Cek ketersediaan ruangan pada tanggal & jam tertentu.
  - **Baris 72:** `Route::get('/rooms', [ComplaintApiController::class, 'rooms'])` — Daftar seluruh ruangan untuk dropdown aplikasi mobile.

### B. Controller Manajemen Ruangan
- **File:** [`app/Http/Controllers/Admin/RoomController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php)
  - **[`index()` (Baris 11–16)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L11-L16):** Mengambil daftar ruangan beserta relasi `pic` dan `activePicAssignments.pic`.
  - **[`create()` (Baris 18–21)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L18-L21):** Menampilkan form tambah ruangan.
  - **[`store()` (Baris 23–35)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L23-L35):** Validasi input dan penyimpanan ruangan baru (`Room::create($validated)`).
  - **[`edit()` (Baris 37–40)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L37-L40):** Menampilkan form edit ruangan.
  - **[`update()` (Baris 42–54)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L42-L54):** Validasi dan pembaruan data ruangan (`$room->update($validated)`).
  - **[`destroy()` (Baris 56–62)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/RoomController.php#L56-L62):** Penghapusan data ruangan (`$room->delete()`).

### C. Model Ruangan
- **File:** [`app/Models/Room.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Room.php)
  - **[`getCategoryAttribute()` (Baris 16–19)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Room.php#L16-L19):** Accessor atribut kategori ruangan berdasarkan lokasi.
  - **[`pic()` (Baris 21–24)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Room.php#L21-L24):** Relasi `belongsTo` ke PIC utama ruangan.
  - **[`activePicAssignments()` & `activePics()` (Baris 26–35)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Room.php#L26-L35):** Relasi untuk mendukung **Multi-PIC** aktif pada satu ruangan (`where('status', 'aktif')`).
  - **[`events()`, `complaints()`, `picAssignments()` (Baris 37–50)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Room.php#L37-L50):** Relasi `hasMany` ke agenda, pengaduan, dan riwayat penugasan PIC.

### D. Views, Seeder & Migration
- **Views:**
  - [`resources/views/admin/rooms/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/rooms/index.blade.php) — Tabel daftar ruangan & badge Multi-PIC.
  - [`resources/views/admin/rooms/create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/rooms/create.blade.php) — Form tambah ruangan.
  - [`resources/views/admin/rooms/edit.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/rooms/edit.blade.php) — Form edit ruangan.
- **Seeder 65 Ruangan Standar:** [`database/seeders/RoomSeeder.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/seeders/RoomSeeder.php#L14-L71) (membuat Ruang 1–35, Lab 1–22, Lab IPA, Lab Penataan Barang, Masjid, Ruang Wijaya, Lapangan, Aula Atas & Aula Bawah).
- **Migration:** [`database/migrations/2025_01_02_000001_create_rooms_table.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/migrations/2025_01_02_000001_create_rooms_table.php)
