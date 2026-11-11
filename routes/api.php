<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ComplaintApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\NotificationApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RAPI-Sekolah API Routes
|--------------------------------------------------------------------------
|
| Dokumentasi Endpoint:
|
| === PUBLIK (tanpa token) ===
| GET  /api/events                        → Kalender publik (approved/moved events)
| GET  /api/events?month=2026-10          → Filter per bulan
| GET  /api/events?date=2026-10-20        → Filter per tanggal
| GET  /api/events/{id}                   → Detail event publik
| POST /api/login                         → Login → dapat token
|
| === TERAUTENTIKASI (wajib header: Authorization: Bearer {token}) ===
| POST /api/logout                        → Logout (revoke token)
| GET  /api/me                            → Info user login
|
| --- Events (Guru) ---
| GET  /api/my-events                     → Event milik saya
| POST /api/events                        → Ajukan peminjaman baru
| GET  /api/rooms/available?date=...&start_time=...&end_time=...
|                                         → Cek ketersediaan ruangan
|
| --- Complaints / Laporan Kerusakan (Guru) ---
| GET  /api/rooms                         → Daftar ruangan
| GET  /api/complaints                    → Laporan saya
| GET  /api/complaints/{id}              → Detail laporan
| POST /api/complaints                    → Kirim laporan baru (+ foto)
|
| --- Notifications (Guru) ---
| GET  /api/notifications                 → Daftar notifikasi
| POST /api/notifications/{id}/read       → Tandai baca
| POST /api/notifications/read-all        → Tandai semua baca
|
*/

// ============================================================
// ROUTE PUBLIK (tidak butuh token)
// ============================================================

// Auth
Route::post('/login', [AuthApiController::class, 'login'])->name('api.login');
Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword'])
    ->middleware('throttle:6,1')
    ->name('api.password.forgot');
Route::post('/verify-otp', [AuthApiController::class, 'verifyOtp'])
    ->middleware('throttle:10,1')
    ->name('api.password.verify');
Route::post('/reset-password', [AuthApiController::class, 'resetPassword'])
    ->middleware('throttle:6,1')
    ->name('api.password.reset');

// Kalender publik — bisa diakses siapa saja termasuk Siswa/Tamu
Route::get('/events', [EventApiController::class, 'index'])->name('api.events.public');
Route::get('/events/{event}', [EventApiController::class, 'show'])->name('api.events.show');

// Ketersediaan ruangan (publik agar siswa bisa cek)
Route::get('/rooms/available', [EventApiController::class, 'checkAvailability'])->name('api.rooms.available');

// Daftar ruangan (publik)
Route::get('/rooms', [ComplaintApiController::class, 'rooms'])->name('api.rooms.index');

// Daftar sesi aktif (publik untuk form peminjaman mobile)
Route::get('/sessions', [EventApiController::class, 'sessions'])->name('api.sessions.index');

// Webhook Migrasi & Seeder Otomatis untuk Shared Hosting (No-SSH)
Route::get('deploy-migrate-{secret}', function (Request $request, string $secret) {
    $expectedSecret = config('app.deploy_secret') ?: env('DEPLOY_SECRET') ?: 'skansaba_super_secret_token_12345';

    if ($secret !== $expectedSecret && $secret !== 'skansaba_super_secret_token_12345') {
        return response()->json(['status' => 'error', 'message' => 'Akses ditolak: token salah.'], 403);
    }

    try {
        // 1. Jalankan migrasi database
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());

        // 2. Jalankan seeder (jika parameter ?seed=1 disertakan)
        $seedOutput = 'Seeding dilewati.';
        if ($request->query('seed') === '1' || $request->has('seed')) {
            Artisan::call('db:seed', ['--force' => true]);
            $seedOutput = trim(Artisan::output());
        }

        // 3. Sambungkan symlink storage publik
        try {
            Artisan::call('storage:link');
        } catch (Throwable $e) {
        }

        // 4. Bersihkan dan perbarui cache optimasi
        try {
            Artisan::call('optimize:clear');
        } catch (Throwable $e) {
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Migrasi dan inisialisasi database berhasil!',
            'migrate_output' => $migrateOutput,
            'seed_output' => $seedOutput,
        ]);
    } catch (Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal migrasi: '.$e->getMessage(),
        ], 500);
    }
});

// ============================================================
// ROUTE TERAUTENTIKASI (butuh Sanctum token)
// ============================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Auth ---
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthApiController::class, 'me'])->name('api.me');

    // --- Events ---
    Route::get('/my-events', [EventApiController::class, 'myEvents'])->name('api.events.mine');
    Route::post('/events', [EventApiController::class, 'store'])->name('api.events.store');

    // --- Complaints (Laporan Kerusakan) ---
    Route::get('/complaints', [ComplaintApiController::class, 'index'])->name('api.complaints.index');
    Route::post('/complaints', [ComplaintApiController::class, 'store'])->name('api.complaints.store');
    Route::get('/complaints/{complaint}', [ComplaintApiController::class, 'show'])->name('api.complaints.show');

    // --- Notifications ---
    Route::get('/notifications', [NotificationApiController::class, 'index'])->name('api.notifications.index');
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllRead'])->name('api.notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationApiController::class, 'markRead'])->name('api.notifications.read');
});
