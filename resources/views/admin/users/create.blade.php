@extends('layouts.app')
@section('title', 'Tambah User')
@section('content')
<div class="container py-4" style="max-width:580px;">
    <h2 class="fw-bold mb-4">➕ Tambah Pengguna Baru</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email *</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Role *</label>
                    <select name="role" class="form-select">
                        @foreach(['admin','pic','guru','organisasi'] as $r)
                            <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ strtoupper($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">No. WhatsApp (62xxx)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="628123456789">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password *</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Konfirmasi Password *</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection