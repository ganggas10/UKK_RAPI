# Fitur 05: Manajemen Pengguna (User Management)

## 1. Deskripsi & Tujuan Fitur
Fitur **Manajemen Pengguna** memungkinkan Waka / Admin untuk mengelola seluruh akun pengguna yang dapat mengakses sistem **RAPI-Sekolah**, mulai dari akun sesama Waka/Admin, PIC Ruang, Guru, hingga Organisasi Siswa. Admin juga dapat mengatur nomor WhatsApp (yang digunakan untuk fitur eskalasi pengaduan ke PIC) serta mengaktifkan/menonaktifkan akun pengguna.

---

## 2. Pengguna (Role) yang Terlibat
- **Waka / Admin (`admin`):** Satu-satunya peran yang memiliki hak akses untuk melihat, membuat, memperbarui, dan menghapus akun pengguna melalui menu **Kelola Pengguna** (`/admin/users`).

---

## 3. Alur Kerja (Workflow) & Logika Bisnis

### A. Melihat Daftar Pengguna (`GET /admin/users`)
Method [`Admin\UserController@index`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L12-L17) mengambil daftar pengguna dengan paginasi 15 data per halaman dan menampilkannya pada [`resources/views/admin/users/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/users/index.blade.php).

### B. Membuat Akun Pengguna Baru (`GET /admin/users/create` & `POST /admin/users`)
Method [`Admin\UserController@store`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L24-L42) memvalidasi input:
- `name`: Wajib diisi, maksimal 100 karakter.
- `email`: Wajib diisi, format email valid, unik di tabel `users`.
- `password`: Wajib diisi, minimal 6 karakter, terkonfirmasi (`confirmed`), dan dienkripsi dengan `Hash::make()`.
- `role`: Wajib dipilih dari `admin`, `pic`, `guru`, `organisasi`, atau `siswa`.
- `phone`: Opsional (maksimal 20 karakter), digunakan untuk menghubungi PIC Ruang via WhatsApp.
- Secara otomatis mengisi `email_verified_at = now()` dan `is_active = true`.

### C. Mengubah Data Pengguna (`PUT /admin/users/{user}`)
Melalui [`Admin\UserController@update`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L49-L69):
- Memvalidasi perubahan nama, email (`unique:users,email,{$user->id}`), role, nomor telepon, dan status aktif (`is_active`).
- Jika kolom password diisi (`$request->filled('password')`), password akan di-hash ulang; jika dikosongkan, password lama tetap dipertahankan.

### D. Menghapus Pengguna & Proteksi Hapus Diri Sendiri (`DELETE /admin/users/{user}`)
Method [`Admin\UserController@destroy`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L71-L80) memeriksa apakah akun yang akan dihapus adalah akun Admin yang sedang login (`$user->id === auth()->id()`). Jika sama, penghapusan ditolak dengan pesan *"Tidak bisa menghapus akun sendiri!"*.

---

## 4. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Manajemen Pengguna
- **File:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L70)
  - **Baris 70:** `Route::resource('users', UserController::class);` — Menghasilkan seluruh rute CRUD pengguna di bawah prefix `/admin/users`.

### B. Controller Manajemen Pengguna
- **File:** [`app/Http/Controllers/Admin/UserController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php)
  - **[`index()` (Baris 12–17)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L12-L17):** Mengambil daftar pengguna (`User::paginate(15)`).
  - **[`create()` (Baris 19–22)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L19-L22):** Menampilkan halaman form tambah pengguna.
  - **[`store()` (Baris 24–42)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L24-L42):** Validasi input (Baris 26–32), enkripsi password (Baris 34), dan pembuatan user baru dengan `is_active = true` (Baris 35–38).
  - **[`edit()` (Baris 44–47)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L44-L47):** Menampilkan halaman form edit pengguna.
  - **[`update()` (Baris 49–69)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L49-L69):** Validasi perubahan data (Baris 51–57), pembaruan password opsional (Baris 59–61), dan penyimpanan status `is_active` (Baris 63–65).
  - **[`destroy()` (Baris 71–80)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/UserController.php#L71-L80):** Proteksi hapus akun sendiri (`if ($user->id === auth()->id())` pada Baris 73–75) dan penghapusan user (Baris 76).

### C. Model & Seeder Pengguna
- **File Model:** [`app/Models/User.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/User.php#L15-L58)
  - **Baris 15–21:** Properti `$fillable` (`name`, `email`, `password`, `role`, `phone`).
  - **Baris 28–32:** Casting `is_active` sebagai `boolean` dan `password` sebagai `hashed`.
- **File Seeder:** [`database/seeders/UserSeeder.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/seeders/UserSeeder.php)
  - **Baris 18–57:** Inisialisasi akun Waka Sarpras, Waka Kurikulum, Waka Kesiswaan, Waka Humas, dan Admin (`pakmail1991@gmail.com`).
  - **Baris 60–97:** Inisialisasi akun PIC Ruang termasuk **Lega Danu Kumara**, **Wahid Abdurrahman**, **Heri Prasetyo**, dan **Arif Kurniawan**.
  - **Baris 100–137:** Inisialisasi akun Guru, Organisasi Siswa (OSIS, MPK, Pramuka, Rohis), dan Siswa.

### D. Views Terkait
- [`resources/views/admin/users/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/users/index.blade.php) — Tabel daftar pengguna.
- [`resources/views/admin/users/create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/users/create.blade.php) — Form tambah pengguna baru.
- [`resources/views/admin/users/edit.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/users/edit.blade.php) — Form edit data pengguna.
