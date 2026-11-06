# PANDUAN DEPLOYMENT STEP-BY-STEP (RAMAH PEMULA)
**Domain:** `yoga-rapi.web.id` (IDwebhost)  
**Hosting:** DomaiNesia (Paket Nimbus One - Tanpa SSH / Tanpa Terminal cPanel)  
**Metode:** Otomatis via GitHub Actions & FTP

> **KABAR BAIK!** Semua file kodingan yang rumit dari panduan temanmu (Bab 5, Bab 6, dan Bab 7) **SUDAH DISIAPKAN OTOMATIS** di dalam proyek ini:
> 1. File `.htaccess` di folder utama sudah dibuat.
> 2. Konfigurasi `deploy_secret` di `config/app.php` sudah dipasang.
> 3. Jalur migrasi otomatis `/api/deploy-migrate-{secret}` di `routes/api.php` sudah dipasang.
> 4. `database/seeders/UserSeeder.php` sudah diamankan dengan `updateOrCreate` agar tidak error duplikat.
> 5. File robot otomatis `.github/workflows/deploy.yml` sudah dibuat khusus untuk domain `yoga-rapi.web.id` dan PHP 8.3.

Kamu tinggal mengikuti langkah klik-klik di bawah ini secara berurutan!

---

## TAHAP 1: Sambungkan Domain IDwebhost ke DomaiNesia
Agar saat orang mengetik `yoga-rapi.web.id`, arahnya masuk ke hosting DomaiNesia kamu.

1. Buka dan login ke **Member IDwebhost** (https://member.idwebhost.com).
2. Klik menu **Domain Saya** -> Klik domain **`yoga-rapi.web.id`**.
3. Di menu sebelah kiri, klik **Nameserver** (atau *Kelola Nameserver*).
4. Pilih opsi **"Gunakan nameserver lain (masukkan di bawah)"**.
5. Isi kotak Nameserver dengan:
   - **Nameserver 1:** `ns1.domainesia.net`
   - **Nameserver 2:** `ns2.domainesia.net`
   *(Kosongkan Nameserver 3 dan 4 jika ada).*
6. Klik tombol **Ganti Name Server**.

> **Catatan:** Setelah diganti, butuh waktu sekitar **15–30 menit** agar tersambung. Sambil menunggu, langsung lanjut ke Tahap 2!

---

## TAHAP 2: Cek Versi PHP & Aktifkan HTTPS (Gembok Hijau SSL) di cPanel
1. Login ke **cPanel DomaiNesia** kamu (bisa lewat dashboard MyDomaiNesia -> pilih Hosting -> klik *Login cPanel*).
2. **Pastikan PHP versi 8.3:**
   - Ketik **`Select PHP Version`** di kotak pencarian atas cPanel lalu klik.
   - Pastikan *Current PHP version* adalah **`8.3`**. Jika masih 8.1 atau 8.2, ubah ke **8.3** lalu klik **Apply**.
3. **Pasang Sertifikat SSL Gratis (HTTPS):**
   - Ketik **`TLS Wizard`** atau **`SSL/TLS Status`** di kotak pencarian atas cPanel.
   - Centang **`yoga-rapi.web.id`** -> pilih **Let's Encrypt Certificate** -> klik **Issue** / **Run AutoSSL**.
   - *(Jika muncul pesan gagal/error merah, artinya Tahap 1 tadi belum genap 30 menit. Biarkan dulu, nanti bisa diklik Issue lagi setelah selesai Tahap 5).*

---

## TAHAP 3: Buat Database MySQL di cPanel DomaiNesia
Siapkan buku catatan atau Notepad di laptopmu untuk mencatat 3 data dari langkah ini!

1. Di cPanel DomaiNesia, cari dan klik menu **`MySQL Database Wizard`** (atau *Database Wizard*).
2. **Step 1 (Buat Nama Database):**
   - Ketik nama: `rapisekolah`
   - Perhatikan nama lengkapnya (ada awalan username cPanel kamu, contoh: `yogarapi_rapisekolah`). **CATAT NAMA LENGKAP INI!**
   - Klik **Next Step**.
3. **Step 2 (Buat User Database):**
   - Username: ketik `admin` (catat nama lengkapnya, contoh: `yogarapi_admin`). **CATAT!**
   - Password: Buat password yang kuat (atau klik *Password Generator*, lalu **COPY & CATAT PASSWORDNYA!**).
   - Klik **Create User**.
4. **Step 3 (Beri Hak Akses):**
   - Centang kotak paling atas: **`ALL PRIVILEGES`**.
   - Klik **Next Step** / **Make Changes**. Selesai!

---

## TAHAP 4: Buat Akun FTP untuk Robot GitHub di cPanel
Agar GitHub bisa mengirim file website kamu ke cPanel secara otomatis.

1. Di cPanel DomaiNesia, cari dan klik menu **`FTP Accounts`**.
2. Isi form pembuatan akun FTP:
   - **Log In:** ketik `deployer` *(nanti otomatis jadi `deployer@yoga-rapi.web.id`)*.
   - **Password:** Buat password dan **CATAT PASSWORD INI!**
   - **Directory (SANGAT PENTING!):** Secara otomatis biasanya tertulis `public_html/yoga-rapi.web.id/deployer` atau `public_html/deployer`. **HAPUS bagian belakangnya sampai HANYA TERSISA tulisan:**
     ```text
     public_html
     ```
   - **Quota:** Pilih `Unlimited`.
3. Klik tombol **Create FTP Account**.

---

## TAHAP 5: Buat File `.env` di File Manager cPanel
Karena file `.env` berisi password rahasia, file ini kita buat langsung di dalam cPanel (cukup sekali saja seumur hidup).

1. Di cPanel DomaiNesia, buka menu **`File Manager`**.
2. Di sebelah kiri, klik folder **`public_html`**.
   *(Jika di dalam `public_html` ada file bawaan seperti `default.html` atau `cgi-bin`, biarkan atau hapus `default.html` saja).*
3. Klik tombol **`Settings`** di pojok kanan atas -> Centang **`Show Hidden Files (dotfiles)`** -> Klik **Save**.
4. Klik tombol **`+ File`** di pojok kiri atas:
   - New File Name: ketik **`.env`** (pakai titik di depan).
   - Klik **Create New File**.
5. Klik kanan pada file **`.env`** yang baru muncul -> pilih **`Edit`** -> klik **Edit** lagi.
6. **Copy-paste** seluruh teks di bawah ini ke dalam file `.env` di cPanel tersebut, lalu **ubah bagian `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`** sesuai catatanmu di **Tahap 3**:

```env
APP_NAME="RAPI-Sekolah"
APP_ENV=production
APP_KEY=base64:VXKtI3t7RkAM5HB714I0WDlv2wJrHZwobiCDTgICLjQ=
APP_DEBUG=false
APP_URL=https://yoga-rapi.web.id
APP_TIMEZONE=Asia/Jakarta

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ISI_NAMA_DATABASE_DARI_TAHAP_3
DB_USERNAME=ISI_USER_DATABASE_DARI_TAHAP_3
DB_PASSWORD="ISI_PASSWORD_DATABASE_DARI_TAHAP_3"

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=ukkrapi@gmail.com
MAIL_PASSWORD=stmruigudysrmgsb
MAIL_FROM_ADDRESS=ukkrapi@gmail.com
MAIL_FROM_NAME="RAPI-Sekolah"

DEPLOY_SECRET=skansaba_super_secret_token_12345
```

7. Klik tombol biru **`Save Changes`** di pojok kanan atas, lalu klik **Close**.

---

## TAHAP 6: Masukkan Kunci Rahasia (Secrets) ke GitHub
Sekarang kita beri tahu GitHub cara masuk ke FTP DomaiNesia kamu.

1. Buka repositori proyek kamu di website **GitHub.com**.
2. Klik tab **`Settings`** (di deretan menu atas repositori).
3. Di menu sebelah kiri bawah, klik **`Secrets and variables`** -> klik **`Actions`**.
4. Klik tombol hijau **`New repository secret`**, lalu masukkan **5 data ini satu per satu**:

| Nomor | Name (Ketik Persis) | Secret (Isi dengan...) |
| :--- | :--- | :--- |
| **1** | `FTP_SERVER` | Ketik `yoga-rapi.web.id` *(atau alamat server cPanel kamu dari URL cPanel, misal: `xxxxx.id.rapidplex.com`)* |
| **2** | `FTP_USERNAME` | `deployer@yoga-rapi.web.id` *(Username FTP dari Tahap 4)* |
| **3** | `FTP_PASSWORD` | Password FTP yang kamu buat di Tahap 4 |
| **4** | `FTP_PORT` | `21` |
| **5** | `DEPLOY_SECRET` | `skansaba_super_secret_token_12345` |

---

## TAHAP 7: Push ke GitHub & Website Langsung Online! 🚀
Semua persiapan di cPanel dan kode sudah selesai! Sekarang tinggal kirim kode dari VS Code / laptopmu ke GitHub.

1. Buka terminal di VS Code (`Ctrl + ~`), lalu jalankan 3 perintah ini satu per satu:
   ```bash
   git add .
   git commit -m "Siapkan konfigurasi deploy otomatis ke DomaiNesia"
   git push origin main
   ```
   *(Catatan: Jika nama branch utama GitHub kamu adalah `master`, ganti `git push origin main` menjadi `git push origin master`).*
2. Buka repositori GitHub kamu di browser -> Klik tab **`Actions`** di bagian atas.
3. Kamu akan melihat lingkaran kuning berputar bernama **"Siapkan konfigurasi deploy otomatis ke DomaiNesia"** / **"Deploy Laravel Blade to DomaiNesia"**.
4. Tunggu sekitar **2–4 menit** sampai berubah menjadi **Centang Hijau (✅)**.
5. Buka browser dan kunjungi: **`https://yoga-rapi.web.id`**!

---

## 💡 Pertanyaan / Bantuan Cepat (Jika Ada Kendala)
- **Bagaimana kalau database belum terisi otomatis saat pertama kali deploy?**
  Kamu bisa memicu migrasi dan pengisian data awal (seeder) secara manual kapan saja cukup dengan membuka link ini di browser kamu:
  👉 `https://yoga-rapi.web.id/api/deploy-migrate-skansaba_super_secret_token_12345?seed=1`
  Jika muncul tulisan `"status": "success"`, berarti seluruh tabel dan akun login (`admin@sekolah.com`, password: `password`) sudah siap dipakai!
- **Bagaimana kalau nanti aku mengubah kodingan di laptop dan mau update websitenya?**
  Tidak perlu buka cPanel lagi! Cukup ketik `git add .`, `git commit -m "update"`, dan `git push origin main`. GitHub akan otomatis meng-update websitemu!

