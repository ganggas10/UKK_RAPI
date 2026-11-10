# Fitur 02: Autentikasi Multi-Role, Hak Akses & Reset Password OTP

## 1. Deskripsi & Tujuan Fitur
Fitur ini menangani seluruh proses keamanan autentikasi pengguna, pembagian hak akses berbasis peran (*Role-Based Access Control*), autentikasi token REST API untuk aplikasi mobile, serta pemulihan kata sandi (*Forgot Password*) menggunakan **kode OTP 6 digit via Email** maupun tautan reset password.

---

## 2. Pengguna (Role) & Pembagian Hak Akses
Sistem memiliki **5 definisi role** pada model [`User`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/User.php):
1. **`admin` (Waka Sarpras / Kurikulum / Kesiswaan / Humas / Admin):** Akses penuh ke portal manajemen web (`/admin/*`).
2. **`pic` (PIC Ruang / Penanggung Jawab Ruangan):** Akses ke portal pengelolaan ruangan & tindak lanjut pengaduan kerusakan (`/pic/*`).
3. **`guru` (Guru):** Akses ke portal pengajuan peminjaman ruangan & pelaporan kerusakan fasilitas baik melalui Web (`/guru/*`) maupun Aplikasi Mobile (`/api/*`).
4. **`organisasi` (Organisasi Siswa - OSIS/MPK/Ekstrakurikuler):** Memiliki hak akses yang setara dengan `guru` untuk mengajukan peminjaman ruangan dan melaporkan kerusakan fasilitas melalui Web (`/guru/*`) maupun Aplikasi Mobile (`/api/*`).
5. **`siswa` (Siswa):** Tidak perlu login; jika diarahkan ke `/dashboard`, sistem secara otomatis mengarahkan kembali ke Kalender Publik (`/`).

---

## 3. Alur Kerja (Workflow) & Logika Bisnis

### A. Login Web & Pengarahan Otomatis (`Redirect Based on Role`)
1. Pengguna memasukkan email dan password pada halaman [`resources/views/auth/login.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/auth/login.blade.php).
2. Setelah kredensial terverifikasi, pengguna diarahkan ke `/dashboard` yang memetakan tujuan berdasarkan `auth()->user()->role`:
   - `admin` -> `admin.dashboard` (`/admin/dashboard`)
   - `pic` -> `pic.dashboard` (`/pic/dashboard`)
   - `guru` atau `organisasi` -> `guru.dashboard` (`/guru/dashboard`)
   - `default` (`siswa`) -> `calendar.public` (`/`)

### B. Proteksi Rute dengan `RoleMiddleware`
Setiap grup rute dilindungi oleh [`RoleMiddleware`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Middleware/RoleMiddleware.php):
1. Memastikan pengguna sudah login (`auth()->check()`).
2. Memeriksa apakah `auth()->user()->role` termasuk dalam daftar role yang diizinkan pada rute tersebut. Jika tidak sesuai, pengguna otomatis dialihkan ke dashboard yang sesuai dengan rolenya.

### C. Autentikasi REST API Mobile (Laravel Sanctum)
Dikelola oleh [`AuthApiController`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/AuthApiController.php):
1. **`POST /api/login`:** Hanya mengizinkan role `guru` dan `organisasi` untuk mendapatkan Bearer Token (`createToken('mobile')`). Role lain akan ditolak dengan HTTP `403`.
2. **`POST /api/logout`:** Mencabut token akses aktif (`currentAccessToken()->delete()`).
3. **`GET /api/me`:** Mengembalikan profil pengguna yang sedang login.

### D. Lupa Password & Reset Password dengan OTP 6 Digit (Mobile API)
1. **`POST /api/forgot-password`:** Menghasilkan kode OTP 6 digit (`random_int(100000, 999999)`), mengirimkannya ke email melalui [`PasswordResetOtpNotification`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Notifications/PasswordResetOtpNotification.php), dan menyimpan hash OTP (`Hash::make($otp)`) ke tabel `password_reset_otps` dengan masa berlaku **10 menit** dan batas maksimal **5 kali percobaan**.
2. **`POST /api/verify-otp`:** Memvalidasi kode OTP 6 digit terhadap hash di database dan mengecek masa kedaluwarsa serta batas percobaan (`attempts < 5`).
3. **`POST /api/reset-password`:** Mengubah password pengguna, menghapus record OTP dari database, dan memicu event `PasswordReset`.

---

## 4. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Autentikasi Web & API
- **File Route Web:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php)
  - **Baris 32:** `require __DIR__.'/auth.php';` — Memuat rute autentikasi web.
  - **Baris 40–47:** Closure `Route::get('/dashboard', ...)` — Logika pengarahan otomatis (*redirect*) ke dashboard masing-masing role setelah login.
  - **Baris 50–53:** Rute manajemen profil pengguna (`/profile`, `/profile/edit`).
  - **Baris 58, 102, 113:** Penerapan middleware `role:admin`, `role:pic`, dan `role:guru,organisasi`.
- **File Route Auth Breeze:** [`routes/auth.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/auth.php#L1-L60)
  - Definisi rute `login`, `logout`, `forgot-password`, `reset-password`, dan `password.update`.
- **File Route API:** [`routes/api.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/api.php)
  - **Baris 53:** `Route::post('/login', [AuthApiController::class, 'login'])`
  - **Baris 54–62:** Rute `/forgot-password`, `/verify-otp`, dan `/reset-password` dengan proteksi *rate-limiting* (`throttle:6,1` dan `throttle:10,1`).
  - **Baris 126–127:** Rute `/logout` dan `/me` di dalam grup middleware `auth:sanctum`.

### B. Middleware Otorisasi Role
- **File:** [`app/Http/Middleware/RoleMiddleware.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Middleware/RoleMiddleware.php)
  - **[`handle()` (Baris 15–33)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Middleware/RoleMiddleware.php#L15-L33):** Memeriksa status login (Baris 17–19) dan mencocokkan role pengguna dengan parameter rute (Baris 21–30).

### C. Controller Autentikasi API & OTP
- **File:** [`app/Http/Controllers/Api/AuthApiController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/AuthApiController.php)
  - **[`login()` (Baris 28–66)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/AuthApiController.php#L28-L66):** Validasi kredensial (Baris 30–41), pembatasan khusus role `guru` & `organisasi` (Baris 44–48), penghapusan token lama & pembuatan token Sanctum baru (Baris 51–53).
  - **[`logout()` (Baris 71–76)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/AuthApiController.php#L71-L76):** Pencabutan token aktif.
  - **[`me()` (Baris 81–92)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/AuthApiController.php#L81-L92):** Mengambil data profil pengguna yang sedang login.
  - **[`forgotPassword()` (Baris 94–140)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/AuthApiController.php#L94-L140):** Pembuatan OTP 6 digit (Baris 109), pengiriman email notifikasi (Baris 112), dan penyimpanan hash OTP ke tabel `password_reset_otps` (Baris 125–134).
  - **[`verifyOtp()` (Baris 142–167)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/AuthApiController.php#L142-L167):** Verifikasi kode OTP 6 digit.
  - **[`resetPassword()` (Baris 169–206)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/AuthApiController.php#L169-L206):** Transaksi update password baru dan penghapusan record OTP (Baris 192–200).
  - **[`isValidOtp()` & `incrementOtpAttempts()` (Baris 208–221)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/AuthApiController.php#L208-L221):** Pengecekan batas percobaan maksimal 5 kali (`attempts < 5`), waktu kedaluwarsa (`expires_at`), dan kecocokan `Hash::check()`.

### D. Model Pengguna & Helper Role
- **File:** [`app/Models/User.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/User.php)
  - **[`isAdmin()`, `isPic()`, `isGuru()`, `isOrganisasi()`, `isMobileUser()` (Baris 35–58)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/User.php#L35-L58):** Method helper pengecekan role pengguna.
  - **[Relasi Eloquent (Baris 61–84)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/User.php#L61-L84):** Relasi `rooms()`, `picAssignments()`, `events()`, `complaints()`, dan `notifications_()`.

### E. Views & Migrations Terkait
- **View Login:** [`resources/views/auth/login.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/auth/login.blade.php)
- **View Profil:** [`resources/views/profile/show.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/profile/show.blade.php) & [`resources/views/profile/edit.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/profile/edit.blade.php)
- **Migrations:**
  - [`database/migrations/0001_01_01_000000_create_users_table.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/migrations/0001_01_01_000000_create_users_table.php)
  - [`database/migrations/2026_04_06_000001_create_password_reset_otps_table.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/migrations/2026_04_06_000001_create_password_reset_otps_table.php)
