<x-guest-layout>
    @section('title', 'Buat Password Baru — RAPI-Sekolah')
    <h2 class="fw-bold mb-2">Buat Password Baru</h2>
    <p class="text-muted mb-4">Buat password baru yang kuat untuk melindungi akun RAPI-Sekolah Anda.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input id="email" class="form-control" type="email" value="{{ $email }}" readonly>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password Baru</label>
            <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="new-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
            <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password">
            @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Simpan Password Baru</button>
    </form>
    <div class="text-center mt-3"><a href="{{ route('login') }}" class="text-muted small">Kembali ke Login</a></div>
</x-guest-layout>
