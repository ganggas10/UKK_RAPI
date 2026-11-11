# 14. Kelola Sesi Operasional & Aturan Peminjaman Minimal H-1

## 📌 Deskripsi Fitur
Fitur **Kelola Sesi** dan **Aturan Peminjaman Minimal H-1** merupakan subsistem pengaturan jadwal operasional sekolah dan penegakan regulasi reservasi sarana-prasarana:
1. **Kelola Sesi Dinamis (Waka/Admin):**
   - Admin dapat menambah, mengubah jam mulai/selesai, mengaktifkan/menonaktifkan, dan menghapus sesi jam pelajaran.
   - Sesi terproteksi dari penghapusan jika sedang aktif digunakan oleh agenda peminjaman yang disetujui atau masih pending.
   - Perubahan jam pada sesi tidak merusak (*non-destructive*) riwayat peminjaman yang sudah disetujui sebelumnya.
2. **Aturan Peminjaman Minimal H-1 (24 Jam Kalender):**
   - Reservasi ruangan wajib diajukan minimal 1 hari sebelum tanggal penggunaan ruangan (`date > today`).
   - Mencegah pengajuan dadakan pada hari-H untuk menjamin kesiapan sarpras dan kebersihan ruangan oleh tim sekolah.
   - Ditegakkan secara ganda melalui validasi frontend (pembatasan atribut `min` kalender HTML5 & peringatan real-time) serta validasi backend Laravel (`after:today`).
3. **Form Peminjaman Fleksibel (1 Hari vs Rentang Hari):**
   - Untuk kegiatan 1 hari, pengguna cukup mengisi **Tanggal Mulai Peminjaman**.
   - Input **Tanggal Selesai Peminjaman** bersifat opsional dan otomatis disamakan dengan tanggal mulai di backend jika tidak diisi, menjamin integritas kolom database (`NOT NULL`).
   - Untuk kegiatan multi-hari, pengguna dapat mengisi tanggal selesai dengan validasi `after_or_equal:start_date`.

---

## 👥 Pengguna & Hak Akses
- **Waka/Admin:** Mengakses menu **Kelola Sesi** (`/admin/sessions`), mengatur daftar sesi jam pelajaran, membuat peminjaman ruangan dengan aturan H-1 & opsi 1 hari.
- **Guru & Organisasi:** Memilih sesi mulai dan sesi selesai dari daftar sesi aktif pada form pengajuan peminjaman ruangan dengan aturan minimal H-1 dan form 1 hari yang ringkas.

---

## 📍 Posisi Source Code (File, Method & Baris)

| Komponen | Lokasi File | Method / Bagian | Keterangan |
| :--- | :--- | :--- | :--- |
| **Model** | [`app/Models/SchoolSession.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/SchoolSession.php) | `scopeActive()`, `events()` | Definisi entitas sesi sekolah, relasi dengan event, dan format waktu. |
| **Controller Admin Sesi** | [`app/Http/Controllers/Admin/SchoolSessionController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/SchoolSessionController.php) | `index()`, `store()`, `update()`, `toggle()`, `destroy()` | Operasi CRUD sesi operasional dan proteksi penghapusan sesi terpakai. |
| **Controller Admin Event** | [`app/Http/Controllers/Admin/EventController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/EventController.php#L32-L55) | `create()`, `store()` | Validasi minimal H-1, fallback `end_date` otomatis 1 hari, dan pengecekan bentrok sesi. |
| **Controller Guru Event** | [`app/Http/Controllers/Guru/EventController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/EventController.php#L34-L57) | `create()`, `store()` | Validasi minimal H-1 dan fallback `end_date` untuk Guru & Organisasi. |
| **Controller API Event** | [`app/Http/Controllers/Api/EventApiController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/EventApiController.php#L129-L152) | `store()` | Validasi API mobile untuk reservasi H-1 dan fallback tanggal selesai 1 hari. |
| **View Manajemen Sesi** | [`resources/views/admin/sessions/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/sessions/index.blade.php) | Tampilan UI Sesi | Tabel daftar sesi, modal tambah/edit sesi, toggle status, dan konfirmasi hapus. |
| **View Form Admin** | [`resources/views/admin/events/create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/events/create.blade.php#L62-L80) | Form & Skrip H-1 / 1 Hari | Input `start_date` wajib, `end_date` opsional, kalkulator durasi hari otomatis. |
| **View Form Guru** | [`resources/views/guru/events/create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/guru/events/create.blade.php#L48-L66) | Form & Skrip H-1 / 1 Hari | Validasi frontend H-1, peringatan merah jika memilih hari ini/lampau, durasi 1 hari default. |
| **Automated Tests** | [`tests/Feature/SchoolSessionAndBookingH1Test.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/tests/Feature/SchoolSessionAndBookingH1Test.php) | Full Test Suite (14 Tests) | Pengujian menyeluruh CRUD sesi, penolakan H-0, reservasi 1 hari tanpa `end_date`, dan API. |
