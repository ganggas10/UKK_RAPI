@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid py-4 px-3 px-lg-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">AKUN PENGGUNA</p>
            <h2 class="fw-bold mb-0">Edit Profil</h2>
        </div>
        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
            Batal
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <form method="POST" action="{{ route('profile.update') }}" novalidate>
                @csrf
                @method('PATCH')

                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                               class="form-control @error('email') is-invalid @enderror" required maxlength="255">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-semibold">Nomor HP</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}"
                               class="form-control @error('phone') is-invalid @enderror" maxlength="20" placeholder="Contoh: 6281234567890">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('profile.show') }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
