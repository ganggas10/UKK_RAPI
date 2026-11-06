<?php

use App\Http\Controllers\Admin\AgendaHistoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\NationalHolidayController;
use App\Http\Controllers\Admin\PicAssignmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Guru\ComplaintController as GuruComplaintController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Guru\EventController as GuruEventController;
use App\Http\Controllers\Pic\ComplaintController as PicComplaintController;
use App\Http\Controllers\Pic\DashboardController as PicDashboard;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ============================================================
// ROUTE PUBLIK — Bisa diakses siapa saja tanpa login
// ============================================================
Route::get('/', [CalendarController::class, 'index'])->name('calendar.public');
Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
Route::get('/calendar/years', [CalendarController::class, 'getYears'])->name('calendar.years');

// ============================================================
// AUTH ROUTES (dari Breeze)
// ============================================================
require __DIR__.'/auth.php';

// ============================================================
// ROUTES YANG BUTUH LOGIN (semua role)
// ============================================================
Route::middleware(['auth'])->group(function () {

    // Kompatibilitas redirect auth Breeze dengan dashboard berbasis role.
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'pic' => redirect()->route('pic.dashboard'),
            'guru', 'organisasi' => redirect()->route('guru.dashboard'),
            default => redirect()->route('calendar.public'),
        };
    })->name('dashboard');

    // Profil pengguna
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================================
    // ADMIN / WAKA ROUTES
    // ============================================================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard

        Route::get('/complaints/{complaint}/whatsapp', [AdminDashboard::class, 'sendWhatsApp'])->name('complaints.whatsapp');

        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::get('/complaints', [AdminDashboard::class, 'complaints'])->name('complaints.index');
        Route::get('/complaints/create', [AdminDashboard::class, 'createComplaint'])->name('complaints.create');
        Route::post('/complaints', [AdminDashboard::class, 'storeComplaint'])->name('complaints.store');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // Manajemen User
        Route::resource('users', UserController::class);

        // Manajemen Ruangan
        Route::resource('rooms', RoomController::class);

        // Manajemen Hari Penting & Kalender Nasional
        Route::post('/national-holidays/sync', [NationalHolidayController::class, 'syncDefault'])->name('national-holidays.sync');
        Route::resource('national-holidays', NationalHolidayController::class);

        // Pembagian tugas PIC Ruang
        Route::get('/pic-assignments/history', [PicAssignmentController::class, 'history'])->name('pic-assignments.history');
        Route::get('/pic-assignments/create', [PicAssignmentController::class, 'create'])->name('pic-assignments.create');
        Route::get('/pic-assignments/{picAssignment}/edit', [PicAssignmentController::class, 'edit'])->name('pic-assignments.edit');
        Route::resource('pic-assignments', PicAssignmentController::class)->except(['create', 'edit']);

        // Manajemen Event (termasuk override/pindah ruang)
        Route::resource('events', AdminEventController::class);
        Route::post('/events/{event}/move', [AdminEventController::class, 'moveRoom'])->name('events.move');

        // Kirim WA Peringatan ke PIC
        Route::get('/complaints/{complaint}/whatsapp', [AdminDashboard::class, 'sendWhatsApp'])->name('complaints.whatsapp');
    });

    // Monitoring read-only seluruh riwayat agenda Waka/Admin.
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/agenda-history', [AgendaHistoryController::class, 'index'])->name('waka.agenda.history');
        Route::get('/agenda-history/{event}', [AgendaHistoryController::class, 'show'])->name('waka.agenda.detail');
    });

    // ============================================================
    // PIC ROUTES
    // ============================================================
    Route::middleware(['role:pic'])->prefix('pic')->name('pic.')->group(function () {
        Route::get('/dashboard', [PicDashboard::class, 'index'])->name('dashboard');

        // Kelola status perbaikan
        Route::get('/complaints', [PicComplaintController::class, 'index'])->name('complaints.index');
        Route::patch('/complaints/{complaint}/status', [PicComplaintController::class, 'updateStatus'])->name('complaints.status');
    });

    // ============================================================
    // GURU / ORGANISASI ROUTES
    // ============================================================
    Route::middleware(['role:guru,organisasi'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [GuruDashboard::class, 'index'])->name('dashboard');

        // Pengajuan Peminjaman Ruang
        Route::resource('events', GuruEventController::class)->only(['index', 'create', 'store', 'show']);

        // Pengaduan Kerusakan
        Route::resource('complaints', GuruComplaintController::class)->only(['index', 'create', 'store', 'show']);

        // Notifikasi sudah dibaca
        Route::post('/notifications/{notification}/read', [GuruDashboard::class, 'markRead'])->name('notifications.read');
    });
});
