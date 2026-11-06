# RAPI-Sekolah API

Import `RAPI-Sekolah.postman_collection.json` ke Postman.

## Menjalankan

1. Jalankan Laravel dari folder `UKK_Ganggas`:
   ```bash
   php artisan serve
   ```
2. Import collection ke Postman.
3. Ubah variable `base_url` menjadi `http://127.0.0.1:8000` atau alamat server Anda.
4. Isi variable `email` dan `password` dengan akun role `guru` atau `organisasi`.
5. Jalankan request `Publik > Login`. Token akan disimpan otomatis ke variable `token`.
6. Isi `room_id` dengan ID dari request `Daftar Ruangan`.
7. Jalankan request pada folder `Mobile Guru Organisasi`.

## Endpoint

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| POST | `/api/login` | Publik | Login dan memperoleh token Sanctum |
| POST | `/api/logout` | Token | Logout dan mencabut token aktif |
| GET | `/api/me` | Token | Profil pengguna aktif |
| GET | `/api/events` | Publik | Kalender event approved/moved |
| GET | `/api/events?month=2026-10` | Publik | Filter event per bulan |
| GET | `/api/events?date=2026-10-22` | Publik | Filter event per tanggal |
| GET | `/api/events/{id}` | Publik/pemilik | Detail event |
| GET | `/api/rooms` | Publik | Daftar ruangan |
| GET | `/api/rooms/available` | Publik | Cek bentrok waktu ruangan |
| GET | `/api/my-events` | Guru/Organisasi | Event milik pengguna |
| POST | `/api/events` | Guru/Organisasi | Pengajuan peminjaman ruangan |
| GET | `/api/complaints` | Guru/Organisasi | Daftar laporan milik pengguna |
| GET | `/api/complaints/{id}` | Guru/Organisasi | Detail laporan milik pengguna |
| POST | `/api/complaints` | Guru/Organisasi | Laporan kerusakan dengan foto opsional |
| GET | `/api/notifications` | Guru/Organisasi | Daftar notifikasi pemindahan ruangan |
| POST | `/api/notifications/{id}/read` | Guru/Organisasi | Tandai satu notifikasi dibaca |
| POST | `/api/notifications/read-all` | Guru/Organisasi | Tandai seluruh notifikasi dibaca |

## Header

Untuk endpoint bertoken gunakan:

```http
Accept: application/json
Authorization: Bearer {token}
```

Request JSON juga memakai:

```http
Content-Type: application/json
```

Untuk laporan dengan foto, gunakan `form-data` dengan field:

- `room_id`: integer, wajib
- `description`: string minimal 10 karakter, wajib
- `photo`: jpg/jpeg/png/webp maksimal 5 MB, wajib

## Skenario fitur utama

### Konflik jadwal

Kirim `POST /api/events` dua kali dengan ruangan, tanggal, dan waktu yang beririsan. Request kedua akan mendapat HTTP `409`.

### Role siswa

Login memakai user role `siswa`. User tetap dapat memakai endpoint publik, tetapi endpoint pengajuan event, complaint, event pribadi, dan notifikasi akan mendapat HTTP `403`.

### WhatsApp, PIC, dan admin

Fitur admin/PIC saat ini merupakan route web berbasis session:

- `/admin/events/{event}/move`
- `/pic/complaints/{complaint}/status`
- `/admin/complaints/{complaint}/whatsapp`

Route tersebut belum menjadi endpoint JSON karena versi mobile yang diminta hanya untuk guru/organisasi dan siswa. Pengujian route web dilakukan melalui browser dengan login session, bukan collection API ini.
