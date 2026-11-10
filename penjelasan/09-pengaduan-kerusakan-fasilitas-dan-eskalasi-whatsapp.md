# Fitur 09: Pengaduan Kerusakan Fasilitas, Tindak Lanjut PIC & Eskalasi WhatsApp

## 1. Deskripsi & Tujuan Fitur
Fitur **Pengaduan Kerusakan Fasilitas** menghubungkan pengguna ruangan (**Guru, Organisasi Siswa, & Admin**), penanggung jawab teknis ruangan (**PIC Ruang**), dan pimpinan sarana-prasarana (**Waka / Admin**) dalam satu alur pelaporan yang transparan:
1. Guru, Organisasi, atau Admin melaporkan kerusakan fasilitas ruangan disertai **unggah foto bukti kerusakan (wajib, maks. 5MB)**.
2. PIC Ruang yang bertugas di ruangan tersebut menerima laporan dan memperbarui progres perbaikan melalui **3 status mutlak**.
3. Waka / Admin memantau seluruh pengaduan dan memiliki tombol **Eskalasi WhatsApp (`wa.me`)** untuk menghubungi PIC Ruang secara langsung apabila ada kerusakan mendesak yang belum diperbaiki.

---

## 2. Pengguna (Role) yang Terlibat
- **Guru & Organisasi (`guru`, `organisasi`):** Membuat laporan pengaduan kerusakan baru (beserta foto) dan memantau progres perbaikannya melalui Web (`/guru/complaints`) atau Aplikasi Mobile (`/api/complaints`).
- **PIC Ruang (`pic`):** Melihat daftar pengaduan khusus di ruangan yang ditugaskan kepadanya dan memperbarui status perbaikan beserta catatan teknis (`/pic/complaints`).
- **Waka / Admin (`admin`):** Memantau seluruh pengaduan (`/admin/complaints`), membuat laporan kerusakan (`/admin/complaints/create`), serta mengirimkan pesan peringatan via WhatsApp ke PIC Ruang.

---

## 3. Tiga Status Mutlak Pengaduan (`status_repair`)
Setiap pengaduan hanya memiliki 3 nilai status standar pada kolom `status_repair`:
1. **`Belum Diperbaiki`** — Status awal saat laporan kerusakan baru dikirimkan.
2. **`Menunggu Spare Part`** — Status ketika PIC sudah memeriksa kerusakan namun perbaikan tertunda karena menunggu pengadaan suku cadang/alat.
3. **`Sudah Diperbaiki`** — Status akhir ketika fasilitas telah selesai diperbaiki oleh PIC Ruang.

---

## 4. Alur Kerja (Workflow) & Logika Bisnis

### A. Pembuatan Laporan Pengaduan & Unggah Foto (`POST /guru/complaints`, `POST /admin/complaints`, `POST /api/complaints`)
1. Pelapor memilih Ruangan (`room_id`), mengisi Deskripsi Kerusakan (`description`, minimal 10 karakter), dan mengunggah Foto Bukti (`photo`).
2. **Validasi File Foto:** Wajib berupa file gambar (`jpg`, `jpeg`, `png`, `webp`) dengan ukuran maksimal **5 MB (`max:5120`)**.
3. Foto disimpan di `storage/app/public/complaints/` (`store('complaints', 'public')`) dan status awal diset ke `'Belum Diperbaiki'`.

### B. Tindak Lanjut & Validasi Delegasi PIC Ruang (`PATCH /pic/complaints/{complaint}/status`)
Dikelola oleh [`Pic\ComplaintController@updateStatus`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Pic/ComplaintController.php#L37-L66):
1. **Validasi Otorisasi Delegasi Ruangan (Termasuk Multi-PIC):**
   - Sebelum PIC dapat mengubah status sebuah pengaduan, sistem memeriksa apakah `room_id` pengaduan tersebut dipegang oleh PIC yang sedang login (baik melalui kolom `pic_id` di tabel `rooms` maupun sebagai ko-PIC aktif di `activePicAssignments`).
   - Jika bukan ruangan tanggung jawabnya, sistem menolak dengan `abort(403, 'Anda tidak memiliki izin untuk mengelola ruangan ini.')`.
2. **Validasi 3 Status Mutlak:**
   - Memvalidasi `status_repair` wajib salah satu dari: `Belum Diperbaiki`, `Menunggu Spare Part`, atau `Sudah Diperbaiki`, beserta catatan opsional `pic_notes`.

### C. Eskalasi WhatsApp dari Waka/Admin ke PIC Ruang (`GET /admin/complaints/{complaint}/whatsapp`)
Dikelola oleh [`Admin\DashboardController@sendWhatsApp`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/DashboardController.php#L82-L102):
1. Sistem memuat data PIC dari ruangan terkait (`$complaint->load('room.pic')`).
2. Memastikan nomor telepon PIC tersedia (`$pic->phone`).
3. Menyusun pesan otomatis berformat tebal (*bold* WhatsApp) yang berisi nama ruangan, deskripsi kerusakan, dan status saat ini, lalu mengarahkan Admin ke `https://wa.me/{$pic->phone}?text={$message}`.

---

## 5. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Pengaduan (Web & API)
- **File Route Web:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php)
  - **Baris 61 & 90:** `Route::get('/complaints/{complaint}/whatsapp', [AdminDashboard::class, 'sendWhatsApp'])->name('complaints.whatsapp');` — Rute eskalasi WhatsApp Admin ke PIC.
  - **Baris 64–66:** Rute monitoring & buat pengaduan oleh Admin (`/admin/complaints`, `/admin/complaints/create`).
  - **Baris 106–107:** Rute daftar & update status pengaduan oleh PIC (`/pic/complaints`, `PATCH /pic/complaints/{complaint}/status`).
  - **Baris 120:** `Route::resource('complaints', GuruComplaintController::class)->only(['index', 'create', 'store', 'show']);` — Rute pengaduan Guru & Organisasi (`/guru/complaints`).
- **File Route API:** [`routes/api.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/api.php#L134-L136)
  - **Baris 134–136:** Rute `GET /api/complaints`, `POST /api/complaints`, dan `GET /api/complaints/{complaint}`.

### B. Controller Pengaduan PIC Ruang
- **File:** [`app/Http/Controllers/Pic/ComplaintController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Pic/ComplaintController.php)
  - **[`index()` (Baris 15–30)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Pic/ComplaintController.php#L15-L30):** Mengambil seluruh pengaduan hanya pada ruangan yang menjadi tanggung jawab PIC tersebut (termasuk Multi-PIC).
  - **[`updateStatus()` (Baris 37–66)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Pic/ComplaintController.php#L37-L66):**
    - **Baris 43–53:** Validasi delegasi ruangan PIC (`abort(403)` jika bukan ruangannya).
    - **Baris 58–63:** Validasi 3 status mutlak (`in:Belum Diperbaiki,Menunggu Spare Part,Sudah Diperbaiki`) dan update `pic_notes`.

### C. Controller Pengaduan Guru & Organisasi (Web)
- **File:** [`app/Http/Controllers/Guru/ComplaintController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/ComplaintController.php)
  - **[`index()` (Baris 12–20)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/ComplaintController.php#L12-L20):** Daftar pengaduan milik pelapor yang sedang login.
  - **[`create()` (Baris 22–27)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/ComplaintController.php#L22-L27):** Menampilkan form lapor kerusakan.
  - **[`store()` (Baris 29–60)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/ComplaintController.php#L29-L60):** Validasi foto maks 5MB (Baris 31–39), upload ke `storage/app/public/complaints` (Baris 44–48), dan simpan dengan status `'Belum Diperbaiki'` (Baris 50–56).
  - **[`show()` (Baris 62–68)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Guru/ComplaintController.php#L62-L68):** Menampilkan detail pengaduan (dilindungi `abort_if($complaint->reporter_id !== auth()->id(), 403)`).

### D. Controller Monitoring & Eskalasi WhatsApp (Waka / Admin)
- **File:** [`app/Http/Controllers/Admin/DashboardController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/DashboardController.php)
  - **[`complaints()` (Baris 41–50)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/DashboardController.php#L41-L50):** Daftar monitoring seluruh pengaduan untuk Admin.
  - **[`createComplaint()` & `storeComplaint()` (Baris 52–80)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/DashboardController.php#L52-L80):** Pembuatan laporan kerusakan oleh Admin.
  - **[`sendWhatsApp()` (Baris 82–102)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/DashboardController.php#L82-L102):** Menyusun URL `https://wa.me/{$pic->phone}?text={$message}` dan mengarahkan Admin langsung ke WhatsApp PIC.

### E. Controller API Mobile (Pengaduan)
- **File:** [`app/Http/Controllers/Api/ComplaintApiController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php)
  - **[`index()` (Baris 17–37)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php#L17-L37):** Daftar pengaduan milik pengguna mobile.
  - **[`show()` (Baris 43–52)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php#L43-L52):** Detail pengaduan beserta catatan PIC (`pic_notes`) dan URL foto (`photo_url`).
  - **[`store()` (Baris 63–101)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Api/ComplaintApiController.php#L63-L101):** Upload multipart foto bukti kerusakan dari kamera/galeri HP dan penyimpanan pengaduan.

### F. Model, Views & Migration
- **Model:** [`app/Models/Complaint.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/Complaint.php#L1-L26)
- **Views:**
  - [`resources/views/guru/complaints/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/guru/complaints/index.blade.php), [`create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/guru/complaints/create.blade.php), [`show.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/guru/complaints/show.blade.php)
  - [`resources/views/pic/complaints/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/pic/complaints/index.blade.php)
  - [`resources/views/admin/complaints/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/complaints/index.blade.php), [`create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/complaints/create.blade.php)
- **Migration:** [`database/migrations/2025_01_02_000004_create_complaints_table.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/migrations/2025_01_02_000004_create_complaints_table.php)
