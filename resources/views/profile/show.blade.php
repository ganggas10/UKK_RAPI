@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@php
    $roleLabels = [
        'admin' => 'Waka / Admin',
        'pic' => 'PIC Ruang',
        'guru' => 'Guru',
        'organisasi' => 'Organisasi',
        'siswa' => 'Siswa',
        'guest' => 'Tamu',
    ];
    $roleLabel = $roleLabels[$user->role] ?? ucfirst($user->role);
    $initials = collect(explode(' ', trim($user->name)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('');
@endphp

<div class="container-fluid py-4 px-3 px-lg-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">AKUN PENGGUNA</p>
            <h2 class="fw-bold mb-0">Profil Saya</h2>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profil</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm profile-card">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-4">
                <div class="profile-avatar">{{ $initials ?: 'U' }}</div>
                <div class="text-center text-sm-start">
                    <h3 class="fw-bold mb-2">{{ $user->name }}</h3>
                    <span class="badge rounded-pill text-bg-primary">{{ $roleLabel }}</span>
                    <p class="text-muted mt-3 mb-0">Informasi akun yang sedang digunakan pada RAPI-Sekolah.</p>
                </div>
            </div>

            <hr class="my-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="profile-detail"><div><small class="text-muted d-block">Nama Lengkap</small><strong>{{ $user->name }}</strong></div></div>
                </div>
                <div class="col-md-4">
                    <div class="profile-detail"><div><small class="text-muted d-block">Email</small><strong class="text-break">{{ $user->email }}</strong></div></div>
                </div>
                <div class="col-md-4">
                    <div class="profile-detail"><div><small class="text-muted d-block">Nomor HP</small><strong>{{ $user->phone ?: 'Belum diisi' }}</strong></div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .profile-card { max-width: 1100px; }
    .profile-avatar {
        width: 88px; height: 88px; flex: 0 0 88px; border-radius: 50%;
        display: grid; place-items: center; background: linear-gradient(135deg, #0d6efd, #6f42c1);
        color: #fff; font-size: 1.8rem; font-weight: 700; box-shadow: 0 8px 20px rgba(13, 110, 253, .2);
    }
    .profile-detail { display: flex; gap: 12px; align-items: flex-start; }
    .profile-detail i { font-size: 1.25rem; margin-top: 2px; }
</style>
@endpush