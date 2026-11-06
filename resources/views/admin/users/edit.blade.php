@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="container py-4" style="max-width:580px;">
    <h2 class="fw-bold mb-4">✏️ Edit Pengguna</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf @method('PATCH')
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Role *</label>
                    <select name="role" class="form-select">
                        @foreach(['admin','pic','guru','organisasi'] as $r)
                            <option value="{{ $r }}" {{ (old('role', $user->role) === $r) ? 'selected' : '' }}>{{ strtoupper($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">No. WhatsApp</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password Baru <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-check mb-4">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $user->is_active))>
                    <label class="form-check-label" for="is_active">Akun aktif dan boleh login</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning">Update</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection