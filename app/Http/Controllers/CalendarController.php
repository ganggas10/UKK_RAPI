<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Event;
use App\Models\NationalHoliday;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Halaman utama kalender publik & beranda RAPI-Sekolah
     */
    public function index(): View
    {
        $categories = NationalHoliday::categories();
        $categoryColors = NationalHoliday::defaultColors();
        $availableYears = Schema::hasTable('national_holidays')
            ? NationalHoliday::distinct()->pluck('year')->sort()->values()->toArray()
            : [];

        $totalRooms = Schema::hasTable('rooms') ? Room::count() : 65;
        $totalApprovedEvents = Schema::hasTable('events')
            ? Event::whereIn('status', ['approved', 'moved'])->count()
            : 0;
        $totalResolvedComplaints = Schema::hasTable('complaints')
            ? Complaint::where('status_repair', 'selesai')->count()
            : 0;

        $apkPath = self::resolveApkPath();
        $apkFileExists = $apkPath !== null;
        $apkDownloadUrl = $apkFileExists ? route('apk.download') : '#unduh-apk';

        return view('calendar.index', compact(
            'categories',
            'categoryColors',
            'availableYears',
            'totalRooms',
            'totalApprovedEvents',
            'totalResolvedComplaints',
            'apkFileExists',
            'apkDownloadUrl',
        ));
    }

    /**
     * Cari lokasi berkas APK yang tersedia di server web maupun folder build mobile
     */
    public static function resolveApkPath(): ?string
    {
        $publicApk = public_path('apk/rapi-sekolah.apk');
        $mobileCandidates = [
            base_path('../mobile/rapi-sekolah.apk'),
            base_path('../mobile/build/app/outputs/flutter-apk/app-release.apk'),
        ];

        foreach ($mobileCandidates as $source) {
            if (is_file($source)) {
                if (! is_file($publicApk) || filemtime($source) > filemtime($publicApk)) {
                    try {
                        if (! is_dir(dirname($publicApk))) {
                            @mkdir(dirname($publicApk), 0775, true);
                        }
                        @copy($source, $publicApk);
                    } catch (\Throwable $e) {
                        // Gunakan $source secara langsung jika gagal menyalin ke public/apk
                    }
                }
                return is_file($publicApk) ? $publicApk : $source;
            }
        }

        return is_file($publicApk) ? $publicApk : null;
    }

    /**
     * Unduh langsung berkas APK RAPI-Sekolah
     */
    public function downloadApk()
    {
        $apkPath = self::resolveApkPath();

        abort_if($apkPath === null, 404, 'Berkas APK belum tersedia.');

        return response()->download($apkPath, 'RAPI-Sekolah.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * API endpoint: kirim data event & hari penting dalam format FullCalendar JSON
     */
    public function getEvents(Request $request): JsonResponse
    {
        // 1. Data Agenda Sekolah (hanya approved & moved)
        $schoolEvents = Event::with(['room', 'user'])
            ->whereIn('status', ['approved', 'moved'])
            ->get()
            ->map(function (Event $event) {
                $startDate = $event->bookingStartDate();
                $endDate = $event->bookingEndDate();
                $startTime = $event->start_time ?: '07:00';
                $endTime = $event->end_time ?: '17:00';

                $creatorRole = $event->creatorRole();
                $creatorColor = $event->creatorColor();
                $creatorBorderColor = $event->creatorBorderColor();
                $creatorRoleLabel = $event->creatorRoleLabel();

                $timeFormatted = $event->session
                    ?: (substr($startTime, 0, 5).' – '.substr($endTime, 0, 5).' WIB');

                $statusLabel = match ($event->status) {
                    'approved' => 'Disetujui',
                    'moved' => 'Dipindahkan',
                    default => ucfirst($event->status),
                };

                return [
                    'id' => $event->id,
                    'title' => ($event->status === 'moved' ? '⚠️ [Dipindah] ' : '🏫 ').$event->title,
                    'start' => $startDate->format('Y-m-d').'T'.$event->start_time,
                    'end' => $endDate->copy()->addDay()->format('Y-m-d').'T'.$event->end_time,
                    'allDay' => false,
                    'backgroundColor' => $creatorColor,
                    'borderColor' => $creatorBorderColor,
                    'textColor' => '#ffffff',
                    'classNames' => ['fc-agenda-event', 'creator-'.$creatorRole],
                    'extendedProps' => [
                        'type' => 'agenda',
                        'raw_id' => $event->id,
                        'title' => $event->title,
                        'room' => $event->room?->name ?? 'Ruangan tidak tercatat',
                        'room_location' => $event->room?->location ?? null,
                        'organizer' => $event->user?->name ?? 'Tidak tersedia',
                        'creator_role' => $creatorRole,
                        'creator_role_label' => $creatorRoleLabel,
                        'creator_color' => $creatorColor,
                        'description' => $event->description ?: 'Tidak ada deskripsi',
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'start_date_formatted' => $startDate->translatedFormat('l, d F Y'),
                        'end_date_formatted' => $endDate->translatedFormat('l, d F Y'),
                        'duration_days' => $event->bookingDurationInDays(),
                        'time_formatted' => $timeFormatted,
                        'status' => $event->status,
                        'status_label' => $statusLabel,
                        'move_reason' => $event->move_reason,
                        'moved_to_room' => $event->movedToRoom?->name,
                    ],
                ];
            });

        // 2. Data Kalender Nasional Indonesia
        $nationalHolidays = Schema::hasTable('national_holidays')
            ? NationalHoliday::all()->map(function (NationalHoliday $holiday) {
                $icon = match ($holiday->category) {
                    NationalHoliday::CATEGORY_LIBUR_NASIONAL => '🔴 ',
                    NationalHoliday::CATEGORY_KEAGAMAAN_ISLAM => '🕌 ',
                    NationalHoliday::CATEGORY_KEAGAMAAN_KRISTEN => '✝️ ',
                    NationalHoliday::CATEGORY_KEAGAMAAN_HINDU => '🪔 ',
                    NationalHoliday::CATEGORY_KEAGAMAAN_BUDDHA => '🪷 ',
                    NationalHoliday::CATEGORY_KEAGAMAAN_KONGHUCU => '🏮 ',
                    NationalHoliday::CATEGORY_CUTI_BERSAMA => '🌸 ',
                    default => '⭐ ',
                };

                return [
                    'id' => 'national-'.$holiday->id,
                    'title' => $icon.$holiday->name,
                    'start' => $holiday->date->format('Y-m-d'),
                    'allDay' => true,
                    'backgroundColor' => $holiday->color,
                    'borderColor' => $holiday->color,
                    'textColor' => $holiday->text_color,
                    'classNames' => ['fc-national-event', 'category-'.$holiday->category],
                    'extendedProps' => [
                        'type' => 'national',
                        'raw_id' => $holiday->id,
                        'name' => $holiday->name,
                        'category' => $holiday->category,
                        'category_label' => $holiday->category_label,
                        'date' => $holiday->date->format('Y-m-d'),
                        'date_formatted' => $holiday->date->translatedFormat('l, d F Y'),
                        'description' => $holiday->description ?: 'Hari penting kalender nasional Indonesia.',
                        'is_holiday' => $holiday->is_holiday,
                        'is_holiday_label' => $holiday->is_holiday_label,
                        'source' => $holiday->source ?: 'Pemerintah Republik Indonesia',
                        'color' => $holiday->color,
                    ],
                ];
            })
            : collect();

        $allEvents = $schoolEvents->concat($nationalHolidays)->values();

        return response()->json($allEvents);
    }

    /**
     * API endpoint: daftar tahun kalender yang tersedia
     */
    public function getYears(): JsonResponse
    {
        $years = Schema::hasTable('national_holidays')
            ? NationalHoliday::distinct()->pluck('year')->sort()->values()
            : collect();

        return response()->json($years);
    }
}
