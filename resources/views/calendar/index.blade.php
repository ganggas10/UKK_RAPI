<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Agenda & Nasional - RAPI-Sekolah</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- FullCalendar CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --calendar-blue: #2563eb;
            --calendar-deep: #1e40af;
            --calendar-soft: #eff6ff;
            --calendar-border: #dbeafe;
            --calendar-ink: #1e293b;
            --calendar-muted: #64748b;
            --color-admin: #0d6efd;
            --color-guru: #198754;
            --color-organisasi: #6f42c1;
            --color-libur: #dc3545;
        }

        * { letter-spacing: 0; }
        body {
            min-height: 100vh;
            color: var(--calendar-ink);
            font-family: 'Manrope', sans-serif;
            background:
                radial-gradient(circle at 8% 0%, rgba(147, 197, 253, 0.55), transparent 28rem),
                radial-gradient(circle at 95% 35%, rgba(219, 234, 254, 0.8), transparent 24rem),
                #f5f9ff;
        }

        .calendar-navbar {
            position: relative;
            min-height: 76px;
            background: linear-gradient(110deg, var(--calendar-deep), var(--calendar-blue) 62%, #60a5fa) !important;
            box-shadow: 0 12px 30px rgba(30, 64, 175, 0.2);
        }

        .calendar-navbar::after {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 1px;
            background: rgba(255, 255, 255, 0.7);
        }

        .calendar-navbar .navbar-brand { font-weight: 800; letter-spacing: -0.04em; }
        .calendar-navbar .btn { border-radius: 11px; font-weight: 700; }
        .calendar-navbar .btn-light { color: var(--calendar-deep); }
        .calendar-navbar .btn-outline-light { background: rgba(255, 255, 255, 0.1); }

        .calendar-page { padding-top: 2.25rem; padding-bottom: 3.5rem; }
        .calendar-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
        .calendar-kicker { margin-bottom: 0.35rem; color: var(--calendar-blue); font-size: 0.76rem; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; }
        .calendar-heading h1 { margin: 0; font-size: clamp(1.55rem, 3vw, 2.2rem); font-weight: 800; letter-spacing: -0.05em; }
        .calendar-heading p { max-width: 650px; margin: 0.45rem 0 0; color: var(--calendar-muted); }
        .readonly-badge { padding: 0.65rem 0.85rem; color: var(--calendar-deep); background: rgba(219, 234, 254, 0.9); border: 1px solid #bfdbfe; border-radius: 999px; font-weight: 800; white-space: nowrap; }

        /* Legend Card */
        .legend-container {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid var(--calendar-border);
            border-radius: 18px;
            padding: 1.15rem 1.35rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.05);
            backdrop-filter: blur(12px);
        }

        .legend-title {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--calendar-deep);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.65rem;
        }

        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 0.75rem;
        }

        .legend-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: var(--calendar-ink);
        }

        .legend-color-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        /* Calendar Wrapper */
        .calendar-wrapper {
            position: relative;
            padding: clamp(1rem, 2.5vw, 1.75rem);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.95);
            border-radius: 22px;
            box-shadow: 0 22px 50px rgba(30, 64, 175, 0.11);
            backdrop-filter: blur(16px);
        }

        .calendar-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 8%;
            width: 38%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #93c5fd, transparent);
        }

        /* FullCalendar Customizations */
        .fc { color: var(--calendar-ink); font-family: inherit; }
        .fc .fc-toolbar { gap: 1rem; margin-bottom: 1.5rem !important; }
        .fc .fc-toolbar-title { color: var(--calendar-ink); font-size: 1.35rem; font-weight: 800; letter-spacing: -0.04em; }
        .fc .fc-button { padding: 0.55rem 0.85rem; color: var(--calendar-deep); background: #fff; border: 1px solid var(--calendar-border); border-radius: 10px; box-shadow: none; font-weight: 700; transition: 0.2s ease; }
        .fc .fc-button:hover, .fc .fc-button:focus { color: #fff; background: var(--calendar-blue); border-color: var(--calendar-blue); box-shadow: 0 6px 14px rgba(37, 99, 235, 0.2); }
        .fc .fc-button-primary:not(:disabled).fc-button-active { color: #fff; background: linear-gradient(135deg, var(--calendar-deep), var(--calendar-blue)); border-color: var(--calendar-blue); }
        
        .fc .fc-daygrid-day { transition: background 0.2s ease; cursor: pointer; }
        .fc .fc-daygrid-day:hover { background: var(--calendar-soft); }
        .fc .fc-daygrid-day-number { padding: 0.5rem 0.65rem; color: var(--calendar-muted); font-weight: 700; text-decoration: none; }
        
        /* Sunday red styling (standar kalender Indonesia) */
        .fc-day-sun .fc-col-header-cell-cushion,
        .fc-day-sun .fc-daygrid-day-number {
            color: #dc3545 !important;
            font-weight: 800 !important;
        }

        /* Tanggal dengan Hari Libur Nasional */
        .fc-day-holiday .fc-daygrid-day-number {
            color: #dc3545 !important;
            font-weight: 800 !important;
        }

        .fc .fc-day-today { background: rgba(219, 234, 254, 0.45) !important; }
        .fc .fc-day-today .fc-daygrid-day-number { color: var(--calendar-deep); font-weight: 800; }
        .fc .fc-col-header-cell-cushion { padding: 0.75rem 0.25rem; color: var(--calendar-muted); font-size: 0.78rem; font-weight: 800; letter-spacing: 0.05em; text-decoration: none; text-transform: uppercase; }
        .fc-theme-standard td, .fc-theme-standard th { border-color: #e5eefc; }
        .fc .fc-scrollgrid { overflow: hidden; border: 1px solid #dbeafe; border-radius: 14px; }
        
        /* Event pill styles */
        .fc-event {
            cursor: pointer;
            margin: 2px 3px;
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 700;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            border-width: 1px;
            border-style: solid;
        }

        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .fc-national-event {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            border-left-width: 4px !important;
        }

        .fc-agenda-event {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-left-width: 4px !important;
        }

        .fc-list-event:hover td { background: var(--calendar-soft); }

        /* Modals */
        .modal-content {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }

        .modal-header {
            padding: 1.15rem 1.4rem;
            color: #fff;
            border: 0;
        }

        .modal-header.bg-agenda {
            background: linear-gradient(110deg, var(--calendar-deep), var(--calendar-blue));
        }

        .modal-header.bg-national {
            background: linear-gradient(110deg, #b91c1c, #dc3545);
        }

        .modal-header.bg-summary {
            background: linear-gradient(110deg, #1e293b, #334155);
        }

        .modal-body { padding: 1.35rem 1.4rem; }
        .modal-footer { border-top: 1px solid #f1f5f9; padding: 0.85rem 1.4rem; }

        .modal-prop-table td {
            padding: 0.55rem 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .modal-prop-table tr:last-child td { border-bottom: 0; }

        @media (max-width: 768px) {
            .calendar-page { padding-top: 1.5rem; }
            .calendar-heading { align-items: flex-start; flex-direction: column; }
            .readonly-badge { align-self: flex-start; }
            .fc .fc-toolbar { align-items: flex-start; flex-direction: column; gap: 0.65rem; }
            .fc .fc-toolbar-chunk { width: 100%; display: flex; justify-content: space-between; }
            .fc .fc-toolbar-chunk:nth-child(2) { order: -1; justify-content: flex-start; }
            .fc .fc-toolbar-title { font-size: 1.15rem; }
            .fc .fc-button { padding: 0.45rem 0.65rem; font-size: 0.78rem; }
            .legend-container { padding: 1rem; }
        }
    </style>
</head>
<body>

{{-- TOP NAVBAR --}}
<nav class="navbar navbar-dark calendar-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('calendar.public') }}">
            RAPI-Sekolah
        </a>
        <div class="d-flex gap-2">
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-sm">Dashboard</a>
                    <a href="{{ route('admin.national-holidays.index') }}" class="btn btn-outline-light btn-sm d-none d-sm-inline-block">Kelola Kalender</a>
                @elseif(auth()->user()->role === 'pic')
                    <a href="{{ route('pic.dashboard') }}" class="btn btn-light btn-sm">Dashboard</a>
                @else
                    <a href="{{ route('guru.dashboard') }}" class="btn btn-light btn-sm">Dashboard</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Login</a>
            @endauth
        </div>
    </div>
</nav>

<div class="container calendar-page">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <div class="calendar-heading">
                <div>
                    <div class="calendar-kicker">RAPI-Sekolah · Kalender Nasional & Agenda Ruang</div>
                    <h1>Kalender Agenda & Nasional</h1>
                    <p>Jadwal agenda kegiatan sekolah terintegrasi dengan kalender nasional Indonesia, hari libur nasional, hari besar keagamaan, dan hari peringatan.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="readonly-badge">Kalender Resmi · Publik</span>
                </div>
            </div>

            {{-- Alert Jika Tahun Belum Tersedia Datanya --}}
            <div id="missingYearAlert" class="alert alert-warning border-warning shadow-sm d-none align-items-center mb-3">
                <span class="fs-4 me-2">⚠️</span>
                <div>
                    <strong>Pemberitahuan Data Kalender Nasional:</strong>
                    <span id="missingYearMessage">Data kalender nasional untuk tahun yang dipilih belum tersedia dalam sistem.</span>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.national-holidays.index') }}" class="btn btn-sm btn-outline-dark ms-2">Perbarui Data</a>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- Legenda Warna Terstruktur --}}
            <div class="legend-container">
                <div class="row g-3">
                    {{-- 1. Legenda Pembuat Agenda Sekolah --}}
                    <div class="col-lg-5 border-end-lg pe-lg-3">
                        <div class="legend-title">🏫 Warna Pembuat Agenda Sekolah</div>
                        <div class="legend-items">
                            <span class="legend-pill" title="Agenda yang dibuat oleh Waka / Admin">
                                <span class="legend-color-dot" style="background-color: var(--color-admin);"></span>
                                Waka / Admin (Biru)
                            </span>
                            <span class="legend-pill" title="Agenda yang dibuat oleh Guru">
                                <span class="legend-color-dot" style="background-color: var(--color-guru);"></span>
                                Guru (Hijau)
                            </span>
                            <span class="legend-pill" title="Agenda yang dibuat oleh Organisasi Siswa">
                                <span class="legend-color-dot" style="background-color: var(--color-organisasi);"></span>
                                Organisasi (Ungu)
                            </span>
                        </div>
                    </div>

                    {{-- 2. Legenda Hari Penting Nasional --}}
                    <div class="col-lg-7 ps-lg-3">
                        <div class="legend-title">🇮🇩 Warna Hari Penting Nasional Indonesia</div>
                        <div class="legend-items">
                            <span class="legend-pill">
                                <span class="legend-color-dot" style="background-color: #dc3545;"></span>
                                Libur Nasional (Merah)
                            </span>
                            <span class="legend-pill">
                                <span class="legend-color-dot" style="background-color: #198754;"></span>
                                Islam (Hijau)
                            </span>
                            <span class="legend-pill">
                                <span class="legend-color-dot" style="background-color: #6f42c1;"></span>
                                Kristen/Katolik (Ungu)
                            </span>
                            <span class="legend-pill">
                                <span class="legend-color-dot" style="background-color: #fd7e14;"></span>
                                Hindu (Oranye)
                            </span>
                            <span class="legend-pill">
                                <span class="legend-color-dot" style="background-color: #ffc107;"></span>
                                Buddha (Kuning)
                            </span>
                            <span class="legend-pill">
                                <span class="legend-color-dot" style="background-color: #d63384;"></span>
                                Konghucu (Merah Muda)
                            </span>
                            <span class="legend-pill">
                                <span class="legend-color-dot" style="background-color: #0d6efd;"></span>
                                Peringatan Nasional (Biru)
                            </span>
                            <span class="legend-pill">
                                <span class="legend-color-dot" style="background-color: #f472b6;"></span>
                                Cuti Bersama (Pink Pucat)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Wrapper Kalender --}}
            <div class="calendar-wrapper">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

{{-- 1. Modal Detail Agenda Sekolah --}}
<div class="modal fade" id="agendaDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-agenda" id="agendaModalHeader">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">🏫</span>
                    <div>
                        <h5 class="modal-title mb-0" id="agendaModalTitle">Detail Agenda Sekolah</h5>
                        <small class="text-white-50" id="agendaModalSubtitle">Agenda Terjadwal</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless modal-prop-table mb-0">
                    <tr>
                        <td class="fw-semibold text-muted" style="width: 38%;">Nama Agenda</td>
                        <td id="agendaTitle" class="fw-bold">-</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Pembuat Agenda</td>
                        <td>
                            <span id="agendaCreatorName" class="fw-bold">-</span>
                            <div class="mt-1">
                                <span id="agendaCreatorRoleBadge" class="badge bg-primary">-</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Ruangan / Lokasi</td>
                        <td>
                            <strong id="agendaRoom">-</strong>
                            <div id="agendaRoomLocation" class="small text-muted"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Rentang Tanggal</td>
                        <td id="agendaDateRange">-</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Waktu Pelaksanaan</td>
                        <td id="agendaTime">-</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Status Agenda</td>
                        <td id="agendaStatus">-</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Deskripsi</td>
                        <td id="agendaDesc" class="text-secondary">-</td>
                    </tr>
                    <tr id="agendaMoveReasonRow" class="d-none">
                        <td class="fw-semibold text-warning">Alasan Pindah</td>
                        <td>
                            <div class="p-2 bg-warning bg-opacity-10 rounded border border-warning">
                                <div id="agendaMoveReason" class="fw-semibold text-dark">-</div>
                                <small class="text-muted" id="agendaMovedToRoom"></small>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- 2. Modal Detail Hari Penting Nasional --}}
<div class="modal fade" id="nationalDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-national" id="nationalModalHeader">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4" id="nationalModalIcon">🇮🇩</span>
                    <div>
                        <h5 class="modal-title mb-0" id="nationalModalTitle">Hari Penting Nasional</h5>
                        <small class="text-white-50" id="nationalModalCategory">Kalender Nasional</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless modal-prop-table mb-0">
                    <tr>
                        <td class="fw-semibold text-muted" style="width: 38%;">Nama Peringatan</td>
                        <td id="nationalName" class="fw-bold fs-6">-</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Kategori</td>
                        <td><span id="nationalCategoryBadge" class="badge">-</span></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Tanggal Lengkap</td>
                        <td id="nationalDate" class="fw-semibold">-</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Status Libur</td>
                        <td id="nationalHolidayStatus">-</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Keterangan / Deskripsi</td>
                        <td id="nationalDescription" class="text-secondary">-</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Sumber Referensi</td>
                        <td id="nationalSource" class="small text-muted">-</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- 3. Modal Rangkuman Agenda & Hari Penting per Tanggal (Klik Sel Tanggal) --}}
<div class="modal fade" id="dateSummaryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-summary">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">📅</span>
                    <div>
                        <h5 class="modal-title mb-0" id="summaryModalDateTitle">Detail Tanggal</h5>
                        <small class="text-white-50">Rangkuman Agenda Sekolah & Hari Penting</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Bagian 1: Hari Penting Nasional --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-2">🇮🇩 Hari Penting / Libur Nasional</h6>
                    <div id="summaryNationalList" class="d-flex flex-column gap-2">
                        {{-- Dimuat via JavaScript --}}
                    </div>
                </div>

                {{-- Bagian 2: Agenda Sekolah --}}
                <div>
                    <h6 class="fw-bold text-success mb-2">🏫 Agenda Sekolah & Peminjaman Ruang</h6>
                    <div id="summaryAgendaList" class="d-flex flex-column gap-2">
                        {{-- Dimuat via JavaScript --}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const agendaModal = new bootstrap.Modal(document.getElementById('agendaDetailModal'));
    const nationalModal = new bootstrap.Modal(document.getElementById('nationalDetailModal'));
    const dateSummaryModal = new bootstrap.Modal(document.getElementById('dateSummaryModal'));

    // Daftar tahun yang didukung kalender nasional
    const availableYears = @json($availableYears);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        firstDay: 1, // Dimulai dari Senin hingga Minggu
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            list:  'Daftar Agenda'
        },
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        events: '{{ route("calendar.events") }}',

        // Pantau perpindahan bulan/tahun untuk memeriksa ketersediaan data kalender nasional
        datesSet: function(dateInfo) {
            const currentYear = dateInfo.view.currentStart.getFullYear();
            const alertEl = document.getElementById('missingYearAlert');
            const msgEl = document.getElementById('missingYearMessage');

            if (availableYears.length > 0 && !availableYears.includes(currentYear)) {
                alertEl.classList.remove('d-none');
                alertEl.classList.add('d-flex');
                msgEl.textContent = `Data kalender nasional untuk tahun ${currentYear} belum tersedia dalam sistem. Kalender saat ini tetap menampilkan agenda sekolah.`;
            } else {
                alertEl.classList.add('d-none');
                alertEl.classList.remove('d-flex');
            }
        },

        // Tandai cell tanggal merah (Minggu & Hari Libur Nasional)
        dayCellDidMount: function(arg) {
            // Hari Minggu
            if (arg.date.getDay() === 0) {
                arg.el.classList.add('fc-day-sun');
            }
        },

        // Klik pada event (agenda atau hari penting)
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const p = info.event.extendedProps;

            if (p.type === 'national') {
                showNationalModal(info.event);
            } else {
                showAgendaModal(info.event);
            }
        },

        // Klik pada sel tanggal untuk melihat seluruh agenda & hari penting pada tanggal tersebut
        dateClick: function(info) {
            showDateSummaryModal(info.dateStr, calendar.getEvents());
        },

        eventDidMount: function(info) {
            const p = info.event.extendedProps;
            if (p.type === 'national') {
                info.el.title = `${p.name} (${p.category_label}) - ${p.is_holiday ? 'Libur Nasional' : 'Hari Peringatan'}`;
                if (p.is_holiday) {
                    // Beri kelas tanda libur pada sel induk
                    const cell = info.el.closest('.fc-daygrid-day');
                    if (cell) {
                        cell.classList.add('fc-day-holiday');
                    }
                }
            } else {
                info.el.title = `${p.title} | ${p.room} (Oleh: ${p.organizer} [${p.creator_role_label}])`;
            }
        }
    });

    calendar.render();

    // 1. Tampilkan Modal Detail Agenda Sekolah
    function showAgendaModal(event) {
        const p = event.extendedProps;
        document.getElementById('agendaTitle').textContent = p.title || '-';
        document.getElementById('agendaCreatorName').textContent = p.organizer || '-';
        
        const roleBadge = document.getElementById('agendaCreatorRoleBadge');
        roleBadge.textContent = p.creator_role_label || 'Pengguna';
        roleBadge.style.backgroundColor = p.creator_color || '#0d6efd';

        document.getElementById('agendaRoom').textContent = p.room || '-';
        document.getElementById('agendaRoomLocation').textContent = p.room_location ? `Lokasi: ${p.room_location}` : '';

        const dateRangeText = (p.duration_days > 1)
            ? `${p.start_date_formatted} s/d ${p.end_date_formatted} (${p.duration_days} hari)`
            : p.start_date_formatted;
        document.getElementById('agendaDateRange').textContent = dateRangeText;
        document.getElementById('agendaTime').textContent = p.time_formatted || '-';

        const statusBadge = p.status === 'moved'
            ? '<span class="badge bg-warning text-dark">Ruangan Dipindahkan</span>'
            : '<span class="badge bg-success">Disetujui</span>';
        document.getElementById('agendaStatus').innerHTML = statusBadge;

        document.getElementById('agendaDesc').textContent = p.description || 'Tidak ada deskripsi.';

        // Alasan pemindahan
        const moveRow = document.getElementById('agendaMoveReasonRow');
        if (p.status === 'moved' && p.move_reason) {
            document.getElementById('agendaMoveReason').textContent = p.move_reason;
            document.getElementById('agendaMovedToRoom').textContent = p.moved_to_room ? `Dipindah ke: ${p.moved_to_room}` : '';
            moveRow.classList.remove('d-none');
        } else {
            moveRow.classList.add('d-none');
        }

        // Sesuaikan header modal sesuai warna pembuat
        document.getElementById('agendaModalHeader').style.background = `linear-gradient(110deg, #1e293b, ${p.creator_color || '#0d6efd'})`;

        agendaModal.show();
    }

    // 2. Tampilkan Modal Detail Hari Penting Nasional
    function showNationalModal(event) {
        const p = event.extendedProps;
        document.getElementById('nationalName').textContent = p.name || '-';
        document.getElementById('nationalModalTitle').textContent = p.name || 'Hari Penting';
        document.getElementById('nationalModalCategory').textContent = p.category_label || 'Kalender Nasional';
        
        const catBadge = document.getElementById('nationalCategoryBadge');
        catBadge.textContent = p.category_label || '-';
        catBadge.style.backgroundColor = p.color || '#dc3545';
        catBadge.style.color = (p.category === 'keagamaan_buddha' || p.category === 'cuti_bersama') ? '#1e293b' : '#ffffff';

        document.getElementById('nationalDate').textContent = p.date_formatted || p.date;

        const holidayBadge = p.is_holiday
            ? '<span class="badge bg-danger">🔴 Hari Libur Nasional (Tanggal Merah)</span>'
            : '<span class="badge bg-primary">Hari Peringatan (Bukan Hari Libur)</span>';
        document.getElementById('nationalHolidayStatus').innerHTML = holidayBadge;

        document.getElementById('nationalDescription').textContent = p.description || 'Tidak ada keterangan tambahan.';
        document.getElementById('nationalSource').textContent = p.source ? `Dasar Penetapan: ${p.source}` : 'Pemerintah Republik Indonesia';

        // Sesuaikan header warna sesuai kategori
        document.getElementById('nationalModalHeader').style.background = `linear-gradient(110deg, #1e293b, ${p.color || '#dc3545'})`;

        nationalModal.show();
    }

    // 3. Tampilkan Modal Rangkuman per Tanggal yang Diklik
    function showDateSummaryModal(dateStr, allEvents) {
        const selectedDate = new Date(dateStr + 'T00:00:00');
        const formattedDate = selectedDate.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        document.getElementById('summaryModalDateTitle').textContent = formattedDate;

        const nationalListEl = document.getElementById('summaryNationalList');
        const agendaListEl = document.getElementById('summaryAgendaList');
        nationalListEl.innerHTML = '';
        agendaListEl.innerHTML = '';

        // Filter event yang mencakup tanggal ini
        const targetDay = dateStr;
        const matchingHolidays = [];
        const matchingAgendas = [];

        allEvents.forEach(evt => {
            const p = evt.extendedProps;
            if (p.type === 'national') {
                if (p.date === targetDay) {
                    matchingHolidays.push(p);
                }
            } else if (p.type === 'agenda') {
                // Periksa rentang tanggal agenda multi-hari
                if (p.start_date <= targetDay && p.end_date >= targetDay) {
                    matchingAgendas.push(p);
                }
            }
        });

        // Tampilkan Hari Penting
        if (matchingHolidays.length === 0) {
            nationalListEl.innerHTML = '<div class="p-2 text-muted fst-italic small bg-light rounded">Tidak ada hari penting atau libur nasional pada tanggal ini.</div>';
        } else {
            matchingHolidays.forEach(h => {
                const item = document.createElement('div');
                item.className = 'p-3 rounded border shadow-sm';
                item.style.borderLeft = `5px solid ${h.color} !important`;
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="fw-bold">${h.name}</div>
                        <span class="badge" style="background-color:${h.color}; color:${(h.category === 'keagamaan_buddha' || h.category === 'cuti_bersama') ? '#1e293b' : '#ffffff'};">${h.category_label}</span>
                    </div>
                    <div class="small text-muted mt-1">${h.description}</div>
                    <div class="mt-2">
                        ${h.is_holiday ? '<span class="badge bg-danger">🔴 Hari Libur Nasional</span>' : '<span class="badge bg-secondary">Hari Peringatan</span>'}
                        <small class="text-muted ms-2">${h.source || ''}</small>
                    </div>
                `;
                nationalListEl.appendChild(item);
            });
        }

        // Tampilkan Agenda Sekolah
        if (matchingAgendas.length === 0) {
            agendaListEl.innerHTML = '<div class="p-2 text-muted fst-italic small bg-light rounded">Tidak ada agenda sekolah yang terjadwal pada tanggal ini.</div>';
        } else {
            matchingAgendas.forEach(a => {
                const item = document.createElement('div');
                item.className = 'p-3 rounded border shadow-sm';
                item.style.borderLeft = `5px solid ${a.creator_color} !important`;
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-bold text-dark">${a.title}</div>
                            <div class="small text-muted">Ruangan: <strong>${a.room}</strong></div>
                        </div>
                        <span class="badge" style="background-color:${a.creator_color}; color:#fff;">${a.creator_role_label}</span>
                    </div>
                    <div class="small text-secondary mt-2">${a.description}</div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <small class="text-muted">🕒 ${a.time_formatted}</small>
                        <small class="text-muted">Diajukan oleh: <strong>${a.organizer}</strong></small>
                    </div>
                `;
                agendaListEl.appendChild(item);
            });
        }

        dateSummaryModal.show();
    }
});
</script>
</body>
</html>
