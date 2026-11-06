<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RAPI-Sekolah')</title>

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>

{{-- =================== TOP NAVBAR =================== --}}
<nav class="navbar navbar-expand-md navbar-light bg-white sticky-top">
    <div class="container-fluid px-3 px-md-4">
        <a class="navbar-brand d-inline-flex align-items-center gap-2 text-dark" href="{{ route('calendar.public') }}">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fw-bold" style="width: 34px; height: 34px; font-size: 0.95rem;">R</span>
            <span>RAPI-Sekolah<span class="text-primary">.</span></span>
        </a>
        <div class="d-flex align-items-center gap-2">
            {{-- Badge notifikasi guru --}}
            @auth
                @if(in_array(auth()->user()->role, ['guru', 'organisasi']))
                    @php
                        $unreadCount = \App\Models\RoomNotification::where('user_id', auth()->id())
                                       ->where('is_read', false)->count();
                    @endphp
                    <a href="{{ route('guru.dashboard') }}" class="btn btn-outline-primary btn-sm position-relative">
                        Notifikasi
                        @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadCount }}
                        </span>
                        @endif
                    </a>
                @endif
            @endauth

            {{-- Dropdown user --}}
            @auth
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <span class="d-none d-sm-inline ms-1">{{ Str::limit(auth()->user()->name, 14) }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-muted small">{{ strtoupper(auth()->user()->role) }}</span></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profil Saya</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
            @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Login</a>
            @endauth
        </div>
    </div>
</nav>

{{-- =================== MAIN WRAPPER =================== --}}
<div class="container-fluid p-0">
    <div class="row g-0">

        {{-- Sidebar (Desktop Only) --}}
        @auth
        <div class="col-md-2 col-lg-2 sidebar d-none d-md-block">
            <nav class="nav flex-column">
                @if(auth()->user()->role === 'admin')
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                       href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                       href="{{ route('admin.users.index') }}">Pengguna</a>
                    <a class="nav-link {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}"
                       href="{{ route('admin.rooms.index') }}">Ruangan</a>
                          <a class="nav-link {{ request()->routeIs('admin.pic-assignments.*') ? 'active' : '' }}"
                              href="{{ route('admin.pic-assignments.index') }}">Manajemen PIC Ruang</a>
                    <a class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}"
                       href="{{ route('admin.events.index') }}">Agenda</a>
                          <a class="nav-link {{ request()->routeIs('admin.complaints.index') ? 'active' : '' }}"
                              href="{{ route('admin.complaints.index') }}">Pengaduan Ruangan</a>
                          <a class="nav-link {{ request()->routeIs('waka.agenda.*') ? 'active' : '' }}"
                               href="{{ route('waka.agenda.history') }}">Riwayat Agenda</a>
                          <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                              href="{{ route('admin.reports.index') }}">Laporan</a>
                    <a class="nav-link {{ request()->routeIs('admin.national-holidays.*') ? 'active' : '' }}"
                       href="{{ route('admin.national-holidays.index') }}">Hari Libur & Peringatan</a>
                    <hr class="mx-3 my-1">

                @elseif(auth()->user()->role === 'pic')
                    <a class="nav-link {{ request()->routeIs('pic.dashboard') ? 'active' : '' }}"
                       href="{{ route('pic.dashboard') }}">Dashboard</a>
                    <a class="nav-link {{ request()->routeIs('pic.complaints.*') ? 'active' : '' }}"
                       href="{{ route('pic.complaints.index') }}">Pengaduan</a>
                    <hr class="mx-3 my-1">

                @elseif(in_array(auth()->user()->role, ['guru', 'organisasi']))
                    <a class="nav-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}"
                       href="{{ route('guru.dashboard') }}">Dashboard</a>
                    <a class="nav-link {{ request()->routeIs('guru.events.*') ? 'active' : '' }}"
                       href="{{ route('guru.events.index') }}">Peminjaman</a>
                    <a class="nav-link {{ request()->routeIs('guru.complaints.*') ? 'active' : '' }}"
                       href="{{ route('guru.complaints.index') }}">Pengaduan</a>
                    <hr class="mx-3 my-1">
                @endif

                <a class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}"
                   href="{{ route('calendar.public') }}">Kalender Publik</a>
                     <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                         href="{{ route('profile.show') }}">Profil Saya</a>
            </nav>
        </div>
        @endauth

        {{-- Main Content Area --}}
        <div class="col-12 {{ auth()->check() ? 'col-md-10' : '' }} main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible m-3 mb-0 fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible m-3 mb-0 fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

{{-- =================== MOBILE BOTTOM NAV =================== --}}
@auth
<nav class="mobile-nav d-md-none">
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('admin.rooms.index') }}" class="{{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">
            Ruangan
        </a>
        <a href="{{ route('admin.events.index') }}" class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
            Agenda
        </a>
        <a href="{{ route('admin.complaints.index') }}" class="{{ request()->routeIs('admin.complaints.index') ? 'active' : '' }}">
            Pengaduan
        </a>
        <a href="{{ route('waka.agenda.history') }}" class="{{ request()->routeIs('waka.agenda.*') ? 'active' : '' }}">
            Riwayat
        </a>
        <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            Laporan
        </a>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            User
        </a>
        <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            Profil
        </a>
    @elseif(auth()->user()->role === 'pic')
        <a href="{{ route('pic.dashboard') }}" class="{{ request()->routeIs('pic.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('pic.complaints.index') }}" class="{{ request()->routeIs('pic.complaints.*') ? 'active' : '' }}">
            Pengaduan
        </a>
        <a href="{{ route('calendar.public') }}">
            Kalender
        </a>
        <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            Profil
        </a>
    @elseif(in_array(auth()->user()->role, ['guru', 'organisasi']))
        <a href="{{ route('guru.dashboard') }}" class="{{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
            Home
        </a>
        <a href="{{ route('guru.events.create') }}">
            Pinjam
        </a>
        <a href="{{ route('guru.complaints.create') }}">
            Lapor
        </a>
        <a href="{{ route('calendar.public') }}">
            Kalender
        </a>
        <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            Profil
        </a>
    @endif
</nav>
@endauth

{{-- =================== SCRIPTS =================== --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>