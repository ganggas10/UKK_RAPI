<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RAPI-Sekolah')</title>

    {{-- Bootstrap 5 & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="rapi-shell-body">

@php
    $currentUser = auth()->user();
    $roleLabels = [
        'admin' => 'Waka / Admin',
        'pic' => 'PIC Ruang',
        'guru' => 'Guru',
        'organisasi' => 'Organisasi',
    ];
    $userRoleLabel = $currentUser ? ($roleLabels[$currentUser->role] ?? ucfirst($currentUser->role)) : 'Tamu';
    $userInitials = $currentUser
        ? collect(explode(' ', trim($currentUser->name)))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->join('')
        : 'R';
@endphp

<div class="rapi-app-frame">
    {{-- =================== LEFT SIDEBAR (DARK NAVY SHELL) =================== --}}
    @auth
    <aside class="rapi-sidebar d-none d-md-flex flex-column justify-content-between">
        <div>
            {{-- Brand Header --}}
            <a href="{{ route('calendar.public') }}" class="rapi-brand-link" title="Kembali ke Beranda & Kalender Publik">
                <span class="rapi-brand-badge">R</span>
                <div class="rapi-brand-text">
                    <span class="rapi-brand-title">RAPI-Sekolah<span class="text-primary">.</span></span>
                    <small class="rapi-brand-sub">{{ $userRoleLabel }}</small>
                </div>
            </a>

            {{-- Navigation Menu --}}
            <nav class="rapi-nav flex-column">
                @if($currentUser->role === 'admin')
                    <a class="rapi-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                       href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}"
                       href="{{ route('admin.events.index') }}">
                        <i class="bi bi-calendar2-check"></i>
                        <span>Agenda</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}"
                       href="{{ route('admin.complaints.index') }}">
                        <i class="bi bi-tools"></i>
                        <span>Pengaduan Ruangan</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}"
                       href="{{ route('admin.rooms.index') }}">
                        <i class="bi bi-door-open"></i>
                        <span>Ruangan</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('admin.pic-assignments.*') ? 'active' : '' }}"
                       href="{{ route('admin.pic-assignments.index') }}">
                        <i class="bi bi-person-badge"></i>
                        <span>Manajemen PIC</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <i class="bi bi-people"></i>
                        <span>Pengguna</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('waka.agenda.*') ? 'active' : '' }}"
                       href="{{ route('waka.agenda.history') }}">
                        <i class="bi bi-clock-history"></i>
                        <span>Riwayat Agenda</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                       href="{{ route('admin.reports.index') }}">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>Laporan</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('admin.national-holidays.*') ? 'active' : '' }}"
                       href="{{ route('admin.national-holidays.index') }}">
                        <i class="bi bi-calendar-heart"></i>
                        <span>Hari Penting</span>
                    </a>

                @elseif($currentUser->role === 'pic')
                    <a class="rapi-nav-link {{ request()->routeIs('pic.dashboard') ? 'active' : '' }}"
                       href="{{ route('pic.dashboard') }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('pic.complaints.*') ? 'active' : '' }}"
                       href="{{ route('pic.complaints.index') }}">
                        <i class="bi bi-tools"></i>
                        <span>Pengaduan</span>
                    </a>

                @elseif(in_array($currentUser->role, ['guru', 'organisasi']))
                    <a class="rapi-nav-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}"
                       href="{{ route('guru.dashboard') }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('guru.events.*') ? 'active' : '' }}"
                       href="{{ route('guru.events.index') }}">
                        <i class="bi bi-calendar-plus"></i>
                        <span>Peminjaman</span>
                    </a>
                    <a class="rapi-nav-link {{ request()->routeIs('guru.complaints.*') ? 'active' : '' }}"
                       href="{{ route('guru.complaints.index') }}">
                        <i class="bi bi-exclamation-octagon"></i>
                        <span>Pengaduan</span>
                    </a>
                @endif

                <div class="rapi-nav-divider"></div>

                <a class="rapi-nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}"
                   href="{{ route('calendar.public') }}">
                    <i class="bi bi-calendar3"></i>
                    <span>Beranda &amp; Kalender</span>
                </a>
                <a class="rapi-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                   href="{{ route('profile.show') }}">
                    <i class="bi bi-person-circle"></i>
                    <span>Profil Saya</span>
                </a>
            </nav>
        </div>

        {{-- Bottom Logout Action (Persis Referensi) --}}
        <div class="rapi-sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="rapi-logout-btn w-100">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </aside>
    @endauth

    {{-- =================== WHITE ROUNDED CANVAS WORKSPACE =================== --}}
    <div class="rapi-canvas">
        {{-- Top Header Inside White Canvas --}}
        <header class="rapi-canvas-header">
            <div class="d-flex align-items-center gap-3">
                {{-- Mobile Brand --}}
                <a href="{{ route('calendar.public') }}" class="d-inline-flex d-md-none align-items-center gap-2 text-decoration-none">
                    <span class="rapi-brand-badge" style="width: 34px; height: 34px; font-size: 0.95rem;">R</span>
                </a>
                <div>
                    <h1 class="rapi-page-heading mb-0">@yield('title', 'Overview')</h1>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 gap-sm-3">
                {{-- Notifikasi Guru/Organisasi (Hanya tampil jika role guru/organisasi karena memang ada fungsinya) --}}
                @auth
                    @if(in_array($currentUser->role, ['guru', 'organisasi']))
                        @php
                            $unreadCount = \App\Models\RoomNotification::where('user_id', auth()->id())
                                           ->where('is_read', false)->count();
                        @endphp
                        <a href="{{ route('guru.dashboard') }}" class="rapi-icon-pill position-relative" title="Notifikasi Ruangan">
                            <i class="bi bi-bell"></i>
                            @if($unreadCount > 0)
                                <span class="rapi-notif-dot">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    @endif

                    {{-- User Pill Dropdown (Persis Pojok Kanan Atas Referensi) --}}
                    <div class="dropdown">
                        <button class="rapi-user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="rapi-user-avatar">{{ $userInitials ?: 'U' }}</span>
                            <span class="rapi-user-name d-none d-sm-inline">{{ Str::limit($currentUser->name, 16) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <div class="px-3 py-2">
                                    <div class="fw-bold text-dark small">{{ $currentUser->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $userRoleLabel }}</div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="bi bi-person me-2 text-primary"></i>Profil Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('calendar.public') }}">
                                    <i class="bi bi-calendar3 me-2 text-primary"></i>Beranda &amp; Kalender
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Login</a>
                @endauth
            </div>
        </header>

        {{-- Main Content Area --}}
        <main class="rapi-canvas-body main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible mb-4 fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible mb-4 fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- =================== MOBILE BOTTOM NAV =================== --}}
@auth
<nav class="mobile-nav d-md-none">
    @if($currentUser->role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i><span>Overview</span>
        </a>
        <a href="{{ route('admin.events.index') }}" class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check"></i><span>Agenda</span>
        </a>
        <a href="{{ route('admin.complaints.index') }}" class="{{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
            <i class="bi bi-tools"></i><span>Pengaduan</span>
        </a>
        <a href="{{ route('admin.rooms.index') }}" class="{{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">
            <i class="bi bi-door-open"></i><span>Ruangan</span>
        </a>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i><span>User</span>
        </a>
        <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person"></i><span>Profil</span>
        </a>
    @elseif($currentUser->role === 'pic')
        <a href="{{ route('pic.dashboard') }}" class="{{ request()->routeIs('pic.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i><span>Overview</span>
        </a>
        <a href="{{ route('pic.complaints.index') }}" class="{{ request()->routeIs('pic.complaints.*') ? 'active' : '' }}">
            <i class="bi bi-tools"></i><span>Pengaduan</span>
        </a>
        <a href="{{ route('calendar.public') }}">
            <i class="bi bi-calendar3"></i><span>Kalender</span>
        </a>
        <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person"></i><span>Profil</span>
        </a>
    @elseif(in_array($currentUser->role, ['guru', 'organisasi']))
        <a href="{{ route('guru.dashboard') }}" class="{{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i><span>Overview</span>
        </a>
        <a href="{{ route('guru.events.create') }}" class="{{ request()->routeIs('guru.events.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-plus"></i><span>Pinjam</span>
        </a>
        <a href="{{ route('guru.complaints.create') }}" class="{{ request()->routeIs('guru.complaints.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-octagon"></i><span>Lapor</span>
        </a>
        <a href="{{ route('calendar.public') }}">
            <i class="bi bi-calendar3"></i><span>Kalender</span>
        </a>
        <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person"></i><span>Profil</span>
        </a>
    @endif
</nav>
@endauth

{{-- =================== SCRIPTS =================== --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
