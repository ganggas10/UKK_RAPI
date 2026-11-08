<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAPI-Sekolah — Beranda & Kalender Agenda Resmi</title>

    {{-- Bootstrap 5 & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- FullCalendar CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
    {{-- Google Fonts: Plus Jakarta Sans / Manrope + Newsreader (Editorial Serif ala Authentik) --}}
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&display=swap" rel="stylesheet">

    <style>
        :root {
            --rapi-blue: #2563eb;
            --rapi-deep: #1e40af;
            --rapi-navy: #0f172a;
            --rapi-soft: #eff6ff;
            --rapi-border: #e2e8f0;
            --rapi-border-blue: #dbeafe;
            --rapi-ink: #0f172a;
            --rapi-muted: #475569;
            --rapi-subtle: #f8fafc;
            --color-admin: #0d6efd;
            --color-guru: #198754;
            --color-organisasi: #6f42c1;
            --color-libur: #dc3545;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            color: var(--rapi-ink);
            font-family: 'Manrope', sans-serif;
            background-color: #ffffff;
            overflow-x: hidden;
        }

        .font-editorial {
            font-family: 'Newsreader', Georgia, serif;
        }

        /* ================= NAVBAR ALA AUTHENTIK ================= */
        .authentik-navbar {
            padding: 1.35rem 0;
            background: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid #f1f5f9;
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            text-decoration: none;
            color: var(--rapi-navy);
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.04em;
        }

        .brand-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--rapi-blue);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.05rem;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
        }

        .authentik-nav-links {
            display: flex;
            align-items: center;
            gap: 1.85rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .authentik-nav-links a {
            color: var(--rapi-muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .authentik-nav-links a:hover {
            color: var(--rapi-blue);
        }

        .authentik-nav-links a.nav-cta-bold {
            color: var(--rapi-blue);
            font-weight: 800;
        }

        /* ================= HERO SECTION ALA AUTHENTIK ================= */
        .hero-section {
            position: relative;
            padding: 4.5rem 0 5rem;
            background:
                radial-gradient(circle at 85% 20%, rgba(219, 234, 254, 0.7), transparent 34rem),
                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border-bottom: 1px solid #eef2f6;
            overflow: hidden;
        }

        .hero-welcome {
            font-size: clamp(1.75rem, 3.2vw, 2.75rem);
            font-weight: 400;
            color: #334155;
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin-bottom: 0.15rem;
        }

        .hero-title {
            font-size: clamp(2.85rem, 6vw, 4.85rem);
            font-weight: 800;
            color: var(--rapi-navy);
            letter-spacing: -0.05em;
            line-height: 1.02;
            margin-bottom: 1.25rem;
        }

        .hero-title span {
            color: var(--rapi-blue);
        }

        .hero-subtitle {
            font-family: 'Newsreader', Georgia, serif;
            font-size: clamp(1.15rem, 1.8vw, 1.38rem);
            color: var(--rapi-muted);
            max-width: 640px;
            line-height: 1.6;
            margin-bottom: 2.15rem;
        }

        .btn-authentik-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.92rem 1.65rem;
            background: var(--rapi-blue);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border-radius: 6px;
            border: 2px solid var(--rapi-blue);
            text-decoration: none;
            box-shadow: 0 12px 26px rgba(37, 99, 235, 0.24);
            transition: all 0.2s ease;
        }

        .btn-authentik-primary:hover {
            background: var(--rapi-deep);
            border-color: var(--rapi-deep);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(30, 64, 175, 0.3);
        }

        .btn-authentik-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.92rem 1.55rem;
            background: #ffffff;
            color: var(--rapi-navy);
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border-radius: 6px;
            border: 2px solid var(--rapi-navy);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-authentik-outline:hover {
            background: var(--rapi-navy);
            color: #ffffff;
            transform: translateY(-2px);
        }

        .hero-text-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--rapi-muted);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-decoration: underline;
            text-underline-offset: 5px;
            transition: color 0.2s ease;
        }

        .hero-text-link:hover {
            color: var(--rapi-blue);
        }

        .hero-badge-caption {
            display: inline-block;
            background: var(--rapi-navy);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            padding: 0.4rem 0.85rem;
            border-radius: 4px;
        }

        /* Hero Atmospheric Graphic Card */
        .hero-visual-card {
            position: relative;
            background: linear-gradient(145deg, #1e3a8a 0%, #2563eb 58%, #60a5fa 100%);
            border-radius: 18px;
            padding: 2rem;
            color: #ffffff;
            box-shadow: 0 28px 65px rgba(30, 64, 175, 0.22);
            overflow: hidden;
        }

        .hero-visual-card::before {
            content: '';
            position: absolute;
            right: -40px;
            bottom: -50px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .stat-grid-mini {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .stat-box-mini {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 12px;
            padding: 0.85rem;
            backdrop-filter: blur(8px);
        }

        .stat-box-mini .num {
            font-size: 1.45rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .stat-box-mini .lbl {
            font-size: 0.72rem;
            opacity: 0.88;
            margin-top: 0.2rem;
        }

        /* ================= SECTION 2: 3 PILAR ALA "START YOUR JOURNEY" ================= */
        .pillars-section {
            padding: 5rem 0;
            background: #ffffff;
        }

        .section-display-title {
            font-size: clamp(2rem, 3.6vw, 2.85rem);
            font-weight: 800;
            color: var(--rapi-navy);
            letter-spacing: -0.04em;
            margin-bottom: 0.75rem;
        }

        .section-editorial-lead {
            font-family: 'Newsreader', Georgia, serif;
            font-size: 1.18rem;
            color: var(--rapi-muted);
            max-width: 620px;
            line-height: 1.65;
            margin-bottom: 3rem;
        }

        .pillar-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            background: var(--rapi-soft);
            border: 1px solid var(--rapi-border-blue);
            color: var(--rapi-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.55rem;
            margin-bottom: 1.25rem;
        }

        .pillar-title {
            font-size: 1.08rem;
            font-weight: 800;
            color: var(--rapi-navy);
            margin-bottom: 0.65rem;
        }

        .pillar-desc {
            font-family: 'Newsreader', Georgia, serif;
            font-size: 1.02rem;
            color: var(--rapi-muted);
            line-height: 1.65;
            margin-bottom: 0;
        }

        /* ================= SECTION 3: CALENDAR AGENDA (SISWA & TAMU) ================= */
        .calendar-section {
            padding: 4.5rem 0 5rem;
            background:
                radial-gradient(circle at 8% 10%, rgba(147, 197, 253, 0.35), transparent 28rem),
                #f8fafc;
            border-top: 1px solid #eef2f6;
            border-bottom: 1px solid #eef2f6;
        }

        .calendar-kicker {
            margin-bottom: 0.35rem;
            color: var(--rapi-blue);
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .readonly-badge {
            padding: 0.6rem 0.95rem;
            color: var(--rapi-deep);
            background: rgba(219, 234, 254, 0.9);
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 800;
            white-space: nowrap;
        }

        /* Legend Card */
        .legend-container {
            background: #ffffff;
            border: 1px solid var(--rapi-border-blue);
            border-radius: 18px;
            padding: 1.15rem 1.35rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.05);
        }

        .legend-title {
            font-size: 0.76rem;
            font-weight: 800;
            color: var(--rapi-deep);
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
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: var(--rapi-ink);
        }

        .legend-color-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        /* Calendar Wrapper (Sesuai Gambar Referensi 2) */
        .calendar-wrapper {
            position: relative;
            padding: clamp(1.1rem, 2.5vw, 1.85rem);
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #dbeafe;
            border-radius: 22px;
            box-shadow: 0 22px 55px rgba(30, 64, 175, 0.09);
        }

        /* FullCalendar Customizations */
        .fc { color: var(--rapi-ink); font-family: 'Manrope', sans-serif; }
        .fc .fc-toolbar { gap: 1rem; margin-bottom: 1.5rem !important; }
        .fc .fc-toolbar-title { color: var(--rapi-ink); font-size: 1.35rem; font-weight: 800; letter-spacing: -0.04em; }
        .fc .fc-button { padding: 0.55rem 0.9rem; color: var(--rapi-deep); background: #fff; border: 1px solid var(--rapi-border-blue); border-radius: 10px; box-shadow: none; font-weight: 700; transition: 0.2s ease; }
        .fc .fc-button:hover, .fc .fc-button:focus { color: #fff; background: var(--rapi-blue); border-color: var(--rapi-blue); box-shadow: 0 6px 14px rgba(37, 99, 235, 0.2); }
        .fc .fc-button-primary:not(:disabled).fc-button-active { color: #fff; background: linear-gradient(135deg, var(--rapi-deep), var(--rapi-blue)); border-color: var(--rapi-blue); }

        .fc .fc-daygrid-day { transition: background 0.2s ease; cursor: pointer; }
        .fc .fc-daygrid-day:hover { background: var(--rapi-soft); }
        .fc .fc-daygrid-day-number { padding: 0.5rem 0.65rem; color: var(--rapi-muted); font-weight: 700; text-decoration: none; }

        /* Sunday red styling */
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
        .fc .fc-day-today .fc-daygrid-day-number { color: var(--rapi-deep); font-weight: 800; }
        .fc .fc-col-header-cell-cushion { padding: 0.75rem 0.25rem; color: var(--rapi-muted); font-size: 0.78rem; font-weight: 800; letter-spacing: 0.05em; text-decoration: none; text-transform: uppercase; }
        .fc-theme-standard td, .fc-theme-standard th { border-color: #e5eefc; }
        .fc .fc-scrollgrid { overflow: hidden; border: 1px solid #dbeafe; border-radius: 14px; }

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

        /* ================= SECTION 4: "GET STARTED TODAY" + PORTAL CARD ================= */
        .get-started-section {
            padding: 5.5rem 0;
            background: #f3f6fb;
        }

        .role-feature-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--rapi-navy);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .role-feature-desc {
            font-family: 'Newsreader', Georgia, serif;
            font-size: 1rem;
            color: var(--rapi-muted);
            line-height: 1.65;
        }

        .authentik-portal-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2.65rem 2.25rem;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.09);
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .portal-action-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem 1.15rem;
            text-align: left;
            margin-bottom: 0.9rem;
        }

        /* ================= FOOTER ALA AUTHENTIK ================= */
        .authentik-footer {
            padding: 4.5rem 0 3rem;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
        }

        .footer-heading {
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--rapi-navy);
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 0.55rem;
        }

        .footer-links a {
            color: var(--rapi-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: var(--rapi-blue);
        }

        /* Modals */
        .modal-content {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 25px 70px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }
        .modal-header { padding: 1.15rem 1.4rem; color: #fff; border: 0; }
        .modal-header.bg-agenda { background: linear-gradient(110deg, var(--rapi-deep), var(--rapi-blue)); }
        .modal-header.bg-national { background: linear-gradient(110deg, #b91c1c, #dc3545); }
        .modal-header.bg-summary { background: linear-gradient(110deg, #0f172a, #1e3a8a); }
        .modal-body { padding: 1.35rem 1.4rem; }
        .modal-footer { border-top: 1px solid #f1f5f9; padding: 0.85rem 1.4rem; }
        .modal-prop-table td { padding: 0.55rem 0; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .modal-prop-table tr:last-child td { border-bottom: 0; }

        @media (max-width: 768px) {
            .hero-section { padding: 2.75rem 0 3.25rem; }
            .pillars-section, .calendar-section, .get-started-section { padding: 3.25rem 0; }
            .fc .fc-toolbar { align-items: flex-start; flex-direction: column; gap: 0.65rem; }
            .fc .fc-toolbar-chunk { width: 100%; display: flex; justify-content: space-between; }
            .fc .fc-toolbar-chunk:nth-child(2) { order: -1; justify-content: flex-start; }
            .fc .fc-toolbar-title { font-size: 1.15rem; }
            .fc .fc-button { padding: 0.45rem 0.65rem; font-size: 0.78rem; }
            .stat-grid-mini { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

{{-- =================== 1. TOP NAVBAR (GAYA AUTHENTIK) =================== --}}
<header class="authentik-navbar">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('calendar.public') }}" class="brand-mark">
                <span class="brand-icon-box">R</span>
                <span>RAPI-Sekolah<span class="text-primary">.</span></span>
            </a>

            <ul class="authentik-nav-links d-none d-md-flex">
                <li><a href="#beranda">Beranda</a></li>
                <li><a href="#fitur-utama">Fitur Sistem</a></li>
                <li><a href="#kalender-agenda">Kalender Agenda</a></li>
                <li>
                    <a href="{{ route('apk.download') }}" download="RAPI-Sekolah.apk">
                        Unduh APK
                    </a>
                </li>
                @auth
                    @php
                        $dashboardUrl = match(auth()->user()->role) {
                            'admin' => route('admin.dashboard'),
                            'pic' => route('pic.dashboard'),
                            default => route('guru.dashboard'),
                        };
                    @endphp
                    <li><a href="{{ $dashboardUrl }}" class="nav-cta-bold">Dashboard ({{ ucfirst(auth()->user()->role) }}) →</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="nav-cta-bold">Masuk Sistem →</a></li>
                @endauth
            </ul>

            {{-- Tombol Mobile --}}
            <div class="d-flex d-md-none gap-2">
                <a href="#kalender-agenda" class="btn btn-outline-primary btn-sm fw-bold">Kalender</a>
                @auth
                    <a href="{{ $dashboardUrl }}" class="btn btn-primary btn-sm fw-bold">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm fw-bold">Login</a>
                @endauth
            </div>
        </div>
    </div>
</header>

@if(session('apk_error'))
    <div class="container mt-3">
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> {{ session('apk_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

{{-- =================== 2. HERO SECTION (GAYA AUTHENTIK) =================== --}}
<section class="hero-section" id="beranda">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-welcome">Selamat datang di</div>
                <h1 class="hero-title">RAPI-Sekolah<span>.</span></h1>
                <p class="hero-subtitle">
                    Platform terintegrasi untuk manajemen peminjaman ruangan, pelaporan kerusakan fasilitas, dan kalender agenda kegiatan sekolah secara transparan &amp; real-time.
                </p>

                {{-- 2 Tombol Utama Sesuai Permintaan User: Login (Admin/Waka & PIC) + Unduh APK --}}
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    @auth
                        <a href="{{ $dashboardUrl }}" class="btn-authentik-primary">
                            <i class="bi bi-speedometer2"></i> Buka Dashboard Sistem
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-authentik-primary">
                            <i class="bi bi-shield-lock-fill"></i> Login Sistem (Waka / Admin &amp; PIC)
                        </a>
                    @endauth

                    <a href="{{ route('apk.download') }}" download="RAPI-Sekolah.apk"
                       class="btn-authentik-outline">
                        <i class="bi bi-android2 text-success"></i> Unduh APK Mobile
                    </a>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-4 pt-1">
                    <a href="#kalender-agenda" class="hero-text-link">
                        LIHAT KALENDER AGENDA TANPA LOGIN <i class="bi bi-arrow-down-short fs-5"></i>
                    </a>
                    <span class="small text-muted">
                        <i class="bi bi-apple me-1"></i> Pengguna iOS (Guru / Organisasi) dapat langsung <a href="{{ route('login') }}" class="fw-bold text-primary text-decoration-none">Login via Web</a>.
                    </span>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="hero-visual-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-white text-primary fw-bold px-3 py-2">
                            <i class="bi bi-check2-circle me-1"></i> Sistem Aktif · Real-Time
                        </span>
                        <span class="small text-white-50 fw-semibold">TA 2026/2027</span>
                    </div>
                    <h4 class="fw-bold text-white mb-2">Rencana Agenda &amp; Penjadwalan Terintegrasi</h4>
                    <p class="small text-white-50 mb-0">
                        Seluruh jadwal ruangan kelas, laboratorium, aula, hingga hari libur nasional tersinkronisasi otomatis untuk mencegah bentrok jadwal di sekolah.
                    </p>

                    <div class="stat-grid-mini">
                        <div class="stat-box-mini">
                            <div class="num">{{ $totalRooms }}</div>
                            <div class="lbl">Ruang &amp; Lab</div>
                        </div>
                        <div class="stat-box-mini">
                            <div class="num">{{ $totalApprovedEvents }}</div>
                            <div class="lbl">Agenda Aktif</div>
                        </div>
                        <div class="stat-box-mini">
                            <div class="num">24/7</div>
                            <div class="lbl">Akses Kalender</div>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 d-flex justify-content-end">
                        <span class="hero-badge-caption">RAPI-Sekolah · Portal Resmi Sarpras</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =================== 3. TIGA PILAR FITUR (ALA "START YOUR JOURNEY") =================== --}}
<section class="pillars-section" id="fitur-utama">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="section-display-title">Kelola fasilitas lebih rapi.</h2>
                <p class="section-editorial-lead">
                    Hadir untuk memudahkan koordinasi antara Waka Sarpras, PIC Ruangan, Guru, Organisasi Siswa, hingga Tamu Sekolah dalam satu ekosistem digital yang jujur dan terukur.
                </p>
            </div>
        </div>

        <div class="row g-4 pt-2">
            <div class="col-md-4">
                <div class="pillar-icon">
                    <i class="bi bi-calendar2-check"></i>
                </div>
                <h3 class="pillar-title">Penjadwalan Anti-Bentrok.</h3>
                <p class="pillar-desc">
                    Pengajuan peminjaman ruang kelas, laboratorium komputer, aula, dan lapangan diverifikasi secara otomatis agar tidak ada jadwal kegiatan yang bertabrakan.
                </p>
            </div>

            <div class="col-md-4">
                <div class="pillar-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <h3 class="pillar-title">Tanggap Pengaduan Fasilitas.</h3>
                <p class="pillar-desc">
                    Laporkan kerusakan sarana ruangan lengkap dengan dokumentasi foto. PIC Ruangan dan Waka Sarpras memantau progres perbaikan hingga tuntas.
                </p>
            </div>

            <div class="col-md-4">
                <div class="pillar-icon">
                    <i class="bi bi-globe-asia-australia"></i>
                </div>
                <h3 class="pillar-title">Kalender Publik &amp; Nasional.</h3>
                <p class="pillar-desc">
                    Siswa dan tamu sekolah dapat langsung melihat jadwal penggunaan ruangan serta hari libur nasional Indonesia kapan saja tanpa perlu login maupun instal aplikasi.
                </p>
            </div>
        </div>

        <div class="mt-4 pt-3">
            <a href="#kalender-agenda" class="hero-text-link">
                JELAJAHI JADWAL KEGIATAN SEKARANG <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

{{-- =================== 4. KALENDER AGENDA PUBLIK (UNTUK SISWA & TAMU) =================== --}}
<section class="calendar-section" id="kalender-agenda">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-4">
                    <div>
                        <div class="calendar-kicker">Akses Terbuka Siswa &amp; Tamu · Tanpa Login</div>
                        <h2 class="section-display-title mb-2">Kalender Agenda &amp; Nasional.</h2>
                        <p class="text-muted mb-0" style="max-width: 680px;">
                            Klik pada tanggal atau label agenda di bawah ini untuk melihat rincian kegiatan sekolah, ruangan yang digunakan, penanggung jawab, serta informasi hari penting nasional.
                        </p>
                    </div>
                    <div>
                        <span class="readonly-badge"><i class="bi bi-eye me-1"></i> Mode Publik · Siswa &amp; Tamu</span>
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

                {{-- Wrapper Kalender Utama --}}
                <div class="calendar-wrapper">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =================== 5. "GET STARTED TODAY" + PORTAL AKSES ROLE =================== --}}
<section class="get-started-section" id="akses-sistem">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <h2 class="section-display-title">Mulai akses sesuai peranmu.</h2>
                <p class="section-editorial-lead mb-4">
                    Ekosistem RAPI-Sekolah dirancang spesifik mengikuti alur kerja setiap warga sekolah—baik melalui dashboard web maupun aplikasi mobile.
                </p>

                <div class="row g-4">
                    <div class="col-sm-6">
                        <div class="role-feature-title">
                            <i class="bi bi-person-gear text-primary"></i> Waka Sarpras / Admin
                        </div>
                        <p class="role-feature-desc mb-0">
                            Menyetujui atau memindahkan agenda ruangan, menugaskan PIC, memantau pengaduan fasilitas, dan mencetak laporan berkala melalui Web.
                        </p>
                    </div>

                    <div class="col-sm-6">
                        <div class="role-feature-title">
                            <i class="bi bi-person-check text-primary"></i> PIC Ruangan
                        </div>
                        <p class="role-feature-desc mb-0">
                            Menerima notifikasi laporan kerusakan ruang tanggung jawabnya dan memperbarui status perbaikan langsung dari portal Web.
                        </p>
                    </div>

                    <div class="col-sm-6">
                        <div class="role-feature-title">
                            <i class="bi bi-phone text-primary"></i> Guru &amp; Organisasi Siswa
                        </div>
                        <p class="role-feature-desc mb-0">
                            Mengajukan peminjaman ruangan dan melaporkan keluhan fasilitas dengan praktis lewat <strong>APK Android</strong> (atau via Web bagi pengguna iOS/iPhone).
                        </p>
                    </div>

                    <div class="col-sm-6">
                        <div class="role-feature-title">
                            <i class="bi bi-people text-primary"></i> Siswa &amp; Tamu Sekolah
                        </div>
                        <p class="role-feature-desc mb-0">
                            Melihat jadwal kegiatan harian, lokasi ruangan, serta kalender libur nasional secara langsung di halaman beranda ini tanpa login.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="authentik-portal-card">
                    <div class="mb-3">
                        <span class="brand-icon-box mx-auto" style="width: 48px; height: 48px; font-size: 1.3rem;">R</span>
                    </div>
                    <h4 class="fw-bold mb-2">Portal Akses RAPI-Sekolah</h4>
                    <p class="font-editorial text-muted mb-4">
                        Pilih jalur akses yang sesuai dengan perangkat dan peran Anda di sekolah.
                    </p>

                    <div class="portal-action-box">
                        <div class="fw-bold small text-dark"><i class="bi bi-laptop text-primary me-1"></i> Portal Web (Admin, Waka, PIC &amp; Pengguna iOS)</div>
                        <div class="small text-muted mt-1">Masuk ke dashboard manajemen menggunakan akun resmi sekolah Anda.</div>
                    </div>

                    <div class="portal-action-box mb-4">
                        <div class="fw-bold small text-dark"><i class="bi bi-android2 text-success me-1"></i> Aplikasi Android (Guru &amp; Organisasi)</div>
                        <div class="small text-muted mt-1">Unduh file instalasi APK resmi untuk peminjaman ruang &amp; lapor kerusakan dari smartphone Android.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn-authentik-primary justify-content-center">
                            <i class="bi bi-box-arrow-in-right"></i> Login ke Sistem Web
                        </a>
                        <a href="{{ route('apk.download') }}" download="RAPI-Sekolah.apk"
                           class="btn-authentik-outline justify-content-center">
                            <i class="bi bi-download"></i> Unduh APK RAPI-Sekolah
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =================== 6. FOOTER ALA AUTHENTIK =================== --}}
<footer class="authentik-footer">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <a href="{{ route('calendar.public') }}" class="brand-mark mb-2">
                    <span class="brand-icon-box">R</span>
                    <span>RAPI-Sekolah<span class="text-primary">.</span></span>
                </a>
                <p class="small text-muted text-uppercase fw-bold mt-2 mb-0" style="letter-spacing: 0.08em;">
                    KELOLA RUANGAN DENGAN TRANSPARAN.
                </p>
            </div>

            <div class="col-lg-8">
                <div class="mb-4 pb-2 border-bottom">
                    <div class="footer-heading mb-1">PRINSIP KAMI</div>
                    <p class="font-editorial fst-italic text-muted mb-0" style="font-size: 1.08rem;">
                        “Keteraturan jadwal dan kesiapan fasilitas sekolah adalah fondasi utama bagi terciptanya kegiatan belajar mengajar yang unggul.”
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-6 col-sm-3">
                        <div class="footer-heading">NAVIGASI</div>
                        <ul class="footer-links">
                            <li><a href="#beranda">Beranda Utama</a></li>
                            <li><a href="#fitur-utama">Fitur Sistem</a></li>
                            <li><a href="#kalender-agenda">Kalender Publik</a></li>
                            <li><a href="#akses-sistem">Portal Akses</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-sm-3">
                        <div class="footer-heading">PERAN SISTEM</div>
                        <ul class="footer-links">
                            <li><a href="{{ route('login') }}">Waka / Admin</a></li>
                            <li><a href="{{ route('login') }}">PIC Ruangan</a></li>
                            <li><a href="{{ route('login') }}">Guru &amp; Staf (Web/iOS)</a></li>
                            <li><a href="{{ route('login') }}">Organisasi Siswa</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-sm-3">
                        <div class="footer-heading">LAYANAN</div>
                        <ul class="footer-links">
                            <li><a href="#kalender-agenda">Jadwal Ruangan</a></li>
                            <li><a href="#kalender-agenda">Kalender Nasional</a></li>
                            <li><a href="{{ route('apk.download') }}" download="RAPI-Sekolah.apk">Unduh APK Android</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-sm-3">
                        <div class="footer-heading">INFORMASI</div>
                        <ul class="footer-links">
                            <li><span class="text-muted small">{{ $totalRooms }} Ruang Terdaftar</span></li>
                            <li><span class="text-muted small">Dukungan Multi-Platform</span></li>
                            <li><span class="text-muted small">yoga-rapi.web.id</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center pt-3 border-top small text-muted">
            <div>&copy; {{ date('Y') }} RAPI-SEKOLAH — HAK CIPTA DILINDUNGI</div>
            <div class="d-flex gap-4 mt-2 mt-sm-0 fw-bold" style="font-size: 0.75rem; letter-spacing: 0.06em;">
                <span>SISTEM MANAJEMEN SARPRAS</span>
                <span>UKK REKAYASA PERANGKAT LUNAK</span>
            </div>
        </div>
    </div>
</footer>

{{-- =================== MODALS =================== --}}

{{-- Modal Info Unduh APK (Muncul jika file APK belum diunggah ke public/apk/rapi-sekolah.apk) --}}
<div class="modal fade" id="apkDownloadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-agenda">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-android2 fs-4"></i>
                    <div>
                        <h5 class="modal-title mb-0">Unduh Aplikasi Mobile (APK)</h5>
                        <small class="text-white-50">RAPI-Sekolah untuk Android</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    Aplikasi mobile <strong>RAPI-Sekolah (.apk)</strong> diperuntukkan bagi <strong>Guru</strong> dan <strong>Organisasi Siswa</strong> untuk mengajukan peminjaman ruangan serta melaporkan kerusakan fasilitas.
                </p>
                <div class="p-3 rounded bg-light border mb-3 small">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle text-primary me-1"></i> Status Berkas APK:</div>
                    <span class="text-muted">Berkas instalasi APK sedang dipersiapkan untuk diunggah ke server. Sementara itu, seluruh pengguna (termasuk pengguna Android &amp; iOS/iPhone) dapat langsung masuk melalui Portal Web.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('login') }}" class="btn btn-primary">Login via Web Sekarang</a>
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

{{-- 3. Modal Rangkuman Agenda & Hari Penting per Tanggal --}}
<div class="modal fade" id="dateSummaryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-summary">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">📅</span>
                    <div>
                        <h5 class="modal-title mb-0" id="summaryModalDateTitle">Detail Tanggal</h5>
                        <small class="text-white-50">Rangkuman Agenda Sekolah &amp; Hari Penting</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="fw-bold text-primary mb-2">🇮🇩 Hari Penting / Libur Nasional</h6>
                    <div id="summaryNationalList" class="d-flex flex-column gap-2"></div>
                </div>

                <div>
                    <h6 class="fw-bold text-success mb-2">🏫 Agenda Sekolah &amp; Peminjaman Ruang</h6>
                    <div id="summaryAgendaList" class="d-flex flex-column gap-2"></div>
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

    const availableYears = @json($availableYears);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        firstDay: 1,
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

        dayCellDidMount: function(arg) {
            if (arg.date.getDay() === 0) {
                arg.el.classList.add('fc-day-sun');
            }
        },

        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const p = info.event.extendedProps;

            if (p.type === 'national') {
                showNationalModal(info.event);
            } else {
                showAgendaModal(info.event);
            }
        },

        dateClick: function(info) {
            showDateSummaryModal(info.dateStr, calendar.getEvents());
        },

        eventDidMount: function(info) {
            const p = info.event.extendedProps;
            if (p.type === 'national') {
                info.el.title = `${p.name} (${p.category_label}) - ${p.is_holiday ? 'Libur Nasional' : 'Hari Peringatan'}`;
                if (p.is_holiday) {
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

        const moveRow = document.getElementById('agendaMoveReasonRow');
        if (p.status === 'moved' && p.move_reason) {
            document.getElementById('agendaMoveReason').textContent = p.move_reason;
            document.getElementById('agendaMovedToRoom').textContent = p.moved_to_room ? `Dipindah ke: ${p.moved_to_room}` : '';
            moveRow.classList.remove('d-none');
        } else {
            moveRow.classList.add('d-none');
        }

        document.getElementById('agendaModalHeader').style.background = `linear-gradient(110deg, #1e293b, ${p.creator_color || '#0d6efd'})`;
        agendaModal.show();
    }

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

        document.getElementById('nationalModalHeader').style.background = `linear-gradient(110deg, #1e293b, ${p.color || '#dc3545'})`;
        nationalModal.show();
    }

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
                if (p.start_date <= targetDay && p.end_date >= targetDay) {
                    matchingAgendas.push(p);
                }
            }
        });

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
