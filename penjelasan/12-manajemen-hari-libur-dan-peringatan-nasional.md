# Fitur 12: Manajemen Kalender Hari Penting & Libur Nasional

## 1. Deskripsi & Tujuan Fitur
Fitur **Manajemen Hari Penting & Libur Nasional** memungkinkan Waka / Admin mengelola daftar tanggal merah, libur keagamaan, cuti bersama, serta hari peringatan nasional yang ditampilkan secara otomatis pada **Kalender Publik (Web)** maupun **Kalender Aplikasi Mobile**.

Fitur ini dilengkapi dengan tombol **Sinkronisasi Otomatis (`syncDefault`)** yang memanggil [`NationalHolidaySeeder`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/seeders/NationalHolidaySeeder.php) untuk memuat data resmi Hari Libur Nasional & Cuti Bersama berdasarkan **SKB 3 Menteri** beserta Hari Peringatan Nasional tanpa menduplikasi data yang sudah ada.

---

## 2. Pengguna (Role) yang Terlibat
- **Waka / Admin (`admin`):** Mengelola data hari penting & libur nasional (Tambah, Edit, Hapus, Filter Tahun/Kategori/Pencarian, dan Sinkronisasi Otomatis) melalui menu **Hari Penting & Libur** (`/admin/national-holidays`).
- **Seluruh Warga Sekolah (Publik, Siswa, Guru, Organisasi, PIC):** Melihat penanda hari libur dan peringatan nasional secara langsung di Kalender Agenda.

---

## 3. Delapan Kategori & Pewarnaan Otomatis (`NationalHoliday`)
Didefinisikan pada model [`NationalHoliday`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/NationalHoliday.php#L12-L64), setiap hari penting dikelompokkan ke dalam 8 kategori spesifik:

| Konstanta Kategori | Label Kategori | Warna Default |
| :--- | :--- | :---: |
| `libur_nasional` | Hari Libur Nasional | Merah (`#dc3545`) |
| `keagamaan_islam` | Hari Besar Keagamaan Islam | Hijau (`#198754`) |
| `keagamaan_kristen` | Hari Besar Keagamaan Kristen/Katolik | Ungu (`#6f42c1`) |
| `keagamaan_hindu` | Hari Besar Keagamaan Hindu | Oranye (`#fd7e14`) |
| `keagamaan_buddha` | Hari Besar Keagamaan Buddha | Kuning (`#ffc107`) |
| `keagamaan_konghucu` | Hari Besar Keagamaan Konghucu | Merah Muda (`#d63384`) |
| `peringatan_nasional` | Hari Peringatan Nasional | Biru (`#0d6efd`) |
| `cuti_bersama` | Cuti Bersama | Merah Muda Pucat (`#f472b6`) |

---

## 4. Alur Kerja (Workflow) & Logika Bisnis

### A. Melihat & Memfilter Daftar Hari Penting (`GET /admin/national-holidays`)
Dikelola oleh [`Admin\NationalHolidayController@index`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php#L14-L39):
- Mendukung filter berdasarkan **Tahun (`year`)**, **Kategori (`category`)**, dan pencarian **Nama Peringatan (`search`)**, diurutkan berdasarkan tanggal secara kronologis (`orderBy('date', 'asc')`).

### B. Menambah & Mengubah Hari Penting (`POST` & `PUT /admin/national-holidays`)
Dikelola oleh [`Admin\NationalHolidayController@store`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php#L49-L80) dan [`update`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php#L90-L121):
1. Mengekstrak tahun otomatis dari input tanggal (`(int) date('Y', strtotime($validated['date']))`).
2. Jika kolom warna (`color`) dikosongkan, sistem otomatis memberikan warna default sesuai kategorinya (`NationalHoliday::defaultColorFor($category)`).
3. **Pencegahan Duplikasi:** Memastikan tidak ada hari penting dengan kombinasi tanggal (`date`) dan nama (`name`) yang sama persis.

### C. Sinkronisasi Otomatis SKB 3 Menteri (`POST /admin/national-holidays/sync`)
Dikelola oleh [`Admin\NationalHolidayController@syncDefault`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php#L131-L145):
- Menjalankan perintah `Artisan::call('db:seed', ['--class' => 'NationalHolidaySeeder', '--force' => true])` untuk mengisi atau memperbarui daftar hari libur nasional dan hari penting resmi pemerintah.

---

## 5. Posisi Source Code (Lokasi File, Method & Baris Kode)

### A. Definisi Route Hari Libur Nasional
- **File:** [`routes/web.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/routes/web.php#L76-L77)
  - **Baris 76:** `Route::post('/national-holidays/sync', [NationalHolidayController::class, 'syncDefault'])->name('national-holidays.sync');`
  - **Baris 77:** `Route::resource('national-holidays', NationalHolidayController::class);`

### B. Controller Hari Libur Nasional
- **File:** [`app/Http/Controllers/Admin/NationalHolidayController.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php)
  - **[`index()` (Baris 14–39)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php#L14-L39):** Filter `year`, `category`, dan `search` serta paginasi 15 data per halaman.
  - **[`create()` & `store()` (Baris 41–80)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php#L41-L80):** Validasi input, ekstraksi `year` otomatis (Baris 61), penetapan warna default (Baris 63–65), pengecekan duplikasi tanggal & nama (Baris 68–74), dan penyimpanan data.
  - **[`edit()` & `update()` (Baris 82–121)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php#L82-L121):** Validasi dan pembaruan data hari penting.
  - **[`destroy()` (Baris 123–129)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php#L123-L129):** Penghapusan data hari penting.
  - **[`syncDefault()` (Baris 131–145)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Http/Controllers/Admin/NationalHolidayController.php#L131-L145):** Eksekusi `NationalHolidaySeeder` via `Artisan::call`.

### C. Model, Views, Seeder & Migration
- **File Model:** [`app/Models/NationalHoliday.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/NationalHoliday.php)
  - **Baris 12–19:** Konstanta 8 kategori hari penting.
  - **[`categories()` & `defaultColors()` (Baris 38–69)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/NationalHoliday.php#L38-L69):** Daftar label kategori dan kode warna HEX.
  - **[`getCategoryLabelAttribute()`, `getIsHolidayLabelAttribute()`, `getTextColorAttribute()` (Baris 71–87)](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/app/Models/NationalHoliday.php#L71-L87):** Accessor label dan kontras warna teks kalender.
- **Views:**
  - [`resources/views/admin/national_holidays/index.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/national_holidays/index.blade.php)
  - [`resources/views/admin/national_holidays/create.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/national_holidays/create.blade.php)
  - [`resources/views/admin/national_holidays/edit.blade.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/resources/views/admin/national_holidays/edit.blade.php)
- **Seeder & Migration:**
  - [`database/seeders/NationalHolidaySeeder.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/seeders/NationalHolidaySeeder.php)
  - [`database/migrations/2026_03_03_000001_create_national_holidays_table.php`](file:///c:/laragon/www/UKK_Ganggas/UKK_Ganggas/database/migrations/2026_03_03_000001_create_national_holidays_table.php)
