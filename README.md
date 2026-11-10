<h1 align="center">🏫 RAPI-Sekolah</h1>
<p align="center">
  <strong>Rencana Agenda & Penjadwalan Terintegrasi Sekolah</strong><br>
  Platform Web & REST API Mobile untuk Manajemen Peminjaman Ruangan, Pelaporan Kerusakan Fasilitas, dan Kalender Agenda Kegiatan Sekolah Secara Real-Time.
</p>

<p align="center">
  <a href="https://yoga-rapi.web.id"><img src="https://img.shields.io/badge/Live_Web-yoga--rapi.web.id-2563eb?style=for-the-badge&logo=google-chrome&logoColor=white" alt="Live Website"></a>
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/FullCalendar-6.1-0d6efd?style=for-the-badge&logo=javascript&logoColor=white" alt="FullCalendar">
  <img src="https://img.shields.io/badge/REST_API-Sanctum-198754?style=for-the-badge&logo=jsonwebtokens&logoColor=white" alt="Laravel Sanctum">
</p>

---

## 📌 Tentang RAPI-Sekolah

**RAPI-Sekolah** adalah sistem informasi manajemen sarana-prasarana dan penjadwalan agenda sekolah berbasis **Web (Laravel)** yang terintegrasi dengan **Aplikasi Mobile Android (APK)** melalui REST API. Sistem ini dirancang untuk menyelesaikan permasalahan bentrok penggunaan ruangan/laboratorium, mempercepat tindak lanjut laporan kerusakan fasilitas sekolah, serta menyajikan kalender agenda kegiatan sekolah yang transparan bagi seluruh warga sekolah.

🌐 **Akses Website Resmi:** [https://yoga-rapi.web.id](https://yoga-rapi.web.id)

---

## 👥 Pembagian Hak Akses Pengguna (Multi-Role)

Sistem membagi hak akses pengguna ke dalam **5 peran (role)** sesuai tugas dan tanggung jawabnya di sekolah:

| Role | Pengguna | Platform Akses | Hak Akses Utama |
| :--- | :--- | :---: | :--- |
| **`admin`** | Waka Sarpras, Kurikulum, Kesiswaan, Humas & Admin | 💻 Portal Web | Mengelola 65 ruangan, akun pengguna, penugasan Multi-PIC, menyetujui/menolak agenda, *override* pindah ruangan, eskalasi WhatsApp ke PIC, memantau riwayat agenda, laporan berkala, & kalender libur nasional. |
| **`pic`** | PIC Ruang (Penanggung Jawab Ruangan/Lab) | 💻 Portal Web | Memantau ruangan tanggung jawabnya dan memperbarui status perbaikan kerusakan fasilitas melalui 3 status mutlak beserta catatan teknis. |
| **`guru`** | Bapak/Ibu Guru | 📱 Mobile APK & 💻 Web | Mengajukan peminjaman ruangan (berbasis Sesi 1–11), melaporkan kerusakan fasilitas disertai foto bukti, serta menerima notifikasi pemindahan ruangan. |
| **`organisasi`** | Pengurus OSIS, MPK & Ekstrakurikuler | 📱 Mobile APK & 💻 Web | Mengajukan peminjaman ruangan untuk kegiatan kesiswaan/organisasi, melaporkan kerusakan fasilitas disertai foto bukti, dan memantau status persetujuan. |
| **`siswa` / Publik** | Siswa & Tamu Sekolah | 🌐 Web & 📱 Mobile (Tanpa Login) | Melihat Kalender Agenda Sekolah & Hari Libur Nasional secara *real-time* tanpa perlu login, serta mengunduh aplikasi APK Mobile. |

---

## ⚙️ 5 Logika Bisnis Utama (Core Business Rules)

1. **Pencegahan Bentrok Jadwal Otomatis (*Conflict Prevention*):**
   Sistem secara otomatis menolak pengajuan peminjaman ruangan apabila ruangan yang dipilih sudah dipesan oleh kegiatan lain (`pending`, `approved`, atau `moved`) pada irisan rentang tanggal dan sesi jam pelajaran yang sama (`Sesi 1` [07:15] s/d `Sesi 11` [15:30]).
2. **Otorisasi Delegasi Ruangan & Multi-PIC (*PIC Room Delegation*):**
   Satu ruangan/laboratorium dapat ditugaskan kepada satu atau beberapa PIC sekaligus (**Multi-PIC**). Setiap PIC Ruang hanya memiliki otorisasi untuk menindaklanjuti laporan pengaduan kerusakan pada ruangan yang secara aktif didelegasikan kepadanya.
3. **Tiga Status Mutlak Perbaikan Fasilitas (*Strict 3-State Workflow*):**
   Setiap laporan kerusakan fasilitas wajib menyertakan unggahan foto bukti (maks. 5 MB) dan hanya dapat diperbarui oleh PIC Ruang melalui 3 tahapan status mutlak:
   - 🔴 `Belum Diperbaiki`
   - 🟡 `Menunggu Spare Part`
   - 🟢 `Sudah Diperbaiki`
4. **Eskalasi Langsung via WhatsApp (*WhatsApp Escalation*):**
   Waka / Admin dapat mengirimkan peringatan tindak lanjut secara langsung ke nomor WhatsApp PIC Ruang terkait hanya dengan satu klik tombol (`wa.me`) dari Dashboard Admin.
5. **Override Pindah Ruangan & Notifikasi Otomatis (*Room Override & Auto-Notification*):**
   Apabila suatu ruangan dibutuhkan untuk kegiatan prioritas sekolah, Waka / Admin dapat memindahkan (*override*) jadwal kegiatan Guru/Organisasi ke ruangan lain yang masih kosong. Sistem otomatis mencatat jejak perpindahan dan mengirimkan notifikasi (`RoomNotification`) ke akun Guru/Organisasi terkait.

---

## 📚 Dokumentasi Lengkap Per Fitur

Penjelasan teknis mendalam beserta lokasi *source code* (file, method, dan nomor baris) untuk setiap fitur tersedia di dalam folder [`penjelasan/`](./penjelasan):

1. [01 - Beranda Utama, Kalender Publik Terintegrasi & Unduh APK Mobile](./penjelasan/01-beranda-dan-kalender-publik.md)
2. [02 - Autentikasi Multi-Role, Hak Akses & Reset Password OTP 6 Digit](./penjelasan/02-autentikasi-dan-manajemen-hak-akses-role.md)
3. [03 - Dashboard Multi-Role (Waka/Admin, PIC Ruang, Guru & Organisasi)](./penjelasan/03-dashboard-multi-role.md)
4. [04 - Manajemen Data 65 Ruangan & Laboratorium Sekolah](./penjelasan/04-manajemen-ruangan.md)
5. [05 - Manajemen Pengguna (User Management)](./penjelasan/05-manajemen-pengguna.md)
6. [06 - Manajemen Penugasan PIC Ruang & Dukungan Multi-PIC](./penjelasan/06-manajemen-penugasan-pic-ruang.md)
7. [07 - Pengajuan Peminjaman Ruangan & Pencegahan Bentrok Jadwal (Anti-Bentrok)](./penjelasan/07-pengajuan-peminjaman-ruangan-dan-anti-bentrok.md)
8. [08 - Persetujuan Agenda & Override Pindah Ruangan oleh Waka/Admin](./penjelasan/08-persetujuan-agenda-dan-pindah-ruang-override.md)
9. [09 - Pengaduan Kerusakan Fasilitas, Tindak Lanjut PIC & Eskalasi WhatsApp](./penjelasan/09-pengaduan-kerusakan-fasilitas-dan-eskalasi-whatsapp.md)
10. [10 - Monitoring & Arsip Riwayat Agenda (Read-Only Waka/Admin)](./penjelasan/10-riwayat-agenda-dan-monitoring.md)
11. [11 - Laporan Berkala Peminjaman & Pengaduan (Harian & Bulanan)](./penjelasan/11-laporan-berkala-sarpras.md)
12. [12 - Manajemen Kalender Hari Penting & Libur Nasional (SKB 3 Menteri)](./penjelasan/12-manajemen-hari-libur-dan-peringatan-nasional.md)
13. [13 - Sistem Notifikasi Ruangan, Integrasi REST API Mobile & Webhook CI/CD](./penjelasan/13-notifikasi-sistem-dan-rest-api-mobile.md)

---

## 🛠️ Teknologi yang Digunakan (Tech Stack)

- **Backend Framework:** Laravel 12.x (PHP 8.3)
- **Database:** MySQL / MariaDB
- **Autentikasi Web & API:** Laravel Breeze & Laravel Sanctum (Bearer Token + Email OTP 6 Digit)
- **Frontend & UI:** Blade Templates, Bootstrap 5, Custom Editorial & Overview Design System, Bootstrap Icons
- **Kalender Interaktif:** FullCalendar v6.1.15
- **Asset Bundler:** Vite
- **CI/CD Deployment:** GitHub Actions (Otomatisasi Build & FTP Deploy ke Shared Hosting)

---

## 🚀 Panduan Instalasi Lokal

1. **Clone repositori ini:**
   ```bash
   git clone https://github.com/ganggas10/UKK_RAPI.git
   cd UKK_RAPI
   ```

2. **Instal dependensi PHP & Frontend:**
   ```bash
   composer install
   npm install
   ```

3. **Salin konfigurasi environment & buat App Key:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi koneksi database di file `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=rapi_sekolah
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan migrasi database, seeder (65 ruangan, akun, & hari libur nasional), dan storage link:**
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

6. **Jalankan aplikasi:**
   ```bash
   npm run build
   php artisan serve
   ```

---

## 🔐 Akun Bawaan (Default Seeder)

Seluruh akun bawaan menggunakan password default: **`password`**

| Peran (Role) | Nama Pengguna | Email Login |
| :--- | :--- | :--- |
| **Waka / Admin** | Waka Sarpras | `waka.sarpras@sekolah.sch.id` |
| **Waka / Admin** | Waka Kurikulum | `waka.kurikulum@sekolah.sch.id` |
| **Waka / Admin** | Pak Mail (Admin) | `pakmail1991@gmail.com` |
| **PIC Ruang** | Lega Danu Kumara *(Lab 2, 4, 6, 8, 11, 14, 19, 21, 22)* | `lega.danu@sekolah.sch.id` |
| **PIC Ruang** | Wahid Abdurrahman *(Lab 3, 7, 9, 16, 17, 20, 21, 22)* | `wahid.abdurrahman@sekolah.sch.id` |
| **PIC Ruang** | Heri Prasetyo *(Lab 1, 5, 7, 10, 12, 13, 18, 21, 22)* | `heri.prasetyo@sekolah.sch.id` |
| **PIC Ruang** | Arif Kurniawan *(Ruang Wijaya)* | `arif.kurniawan@sekolah.sch.id` |
| **Guru** | Budi Santoso, S.Pd | `guru.budi@sekolah.sch.id` |
| **Organisasi Siswa** | OSIS SMK Negeri | `osis@sekolah.sch.id` |

---

## 📱 Ringkasan Endpoint REST API Mobile (`/api/*`)

| Method | Endpoint | Auth | Deskripsi |
| :---: | :--- | :---: | :--- |
| `GET` | `/api/events` | Publik | Daftar agenda sekolah (`approved` & `moved`) untuk kalender mobile |
| `GET` | `/api/rooms` | Publik | Daftar 65 ruangan sekolah beserta kapasitas & lokasi |
| `GET` | `/api/rooms/available` | Publik | Cek ketersediaan ruangan pada tanggal & jam tertentu |
| `POST` | `/api/login` | Publik | Login khusus role `guru` & `organisasi` (menghasilkan Sanctum Token) |
| `POST` | `/api/forgot-password` | Publik | Kirim kode OTP 6 digit reset password ke email |
| `POST` | `/api/verify-otp` | Publik | Verifikasi kode OTP 6 digit |
| `POST` | `/api/reset-password` | Publik | Simpan password baru menggunakan OTP yang terverifikasi |
| `GET` | `/api/me` | Bearer Token | Informasi profil pengguna yang sedang login |
| `POST` | `/api/logout` | Bearer Token | Logout & cabut token akses mobile |
| `GET` | `/api/my-events` | Bearer Token | Riwayat pengajuan peminjaman ruangan milik pengguna |
| `POST` | `/api/events` | Bearer Token | Ajukan peminjaman ruangan baru (Sesi 1–11 + validasi anti-bentrok) |
| `GET` | `/api/complaints` | Bearer Token | Daftar laporan pengaduan kerusakan milik pengguna |
| `POST` | `/api/complaints` | Bearer Token | Kirim laporan kerusakan fasilitas baru + unggah foto bukti (`multipart/form-data`) |
| `GET` | `/api/notifications` | Bearer Token | Daftar notifikasi pemindahan ruangan & jumlah belum dibaca |
| `POST` | `/api/notifications/read-all` | Bearer Token | Tandai seluruh notifikasi sebagai sudah dibaca |

---

<p align="center">
  Dikembangkan untuk Proyek <strong>UKK — RAPI-Sekolah</strong> © 2026
</p>
