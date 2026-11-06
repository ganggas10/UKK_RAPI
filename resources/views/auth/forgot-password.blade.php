<x-guest-layout>
    @section('title', 'Lupa Password? — RAPI-Sekolah')
    <h2 class="fw-bold mb-2">Lupa Password?</h2>
    <p class="text-muted mb-4">Masukkan email yang terdaftar. Kode OTP akan dikirim dari email RAPI-Sekolah untuk memverifikasi permintaan reset password.</p>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Kirim Kode OTP</button>
    </form>
    <div class="text-center mt-3"><a href="{{ route('login') }}" class="text-muted small">Kembali ke Login</a></div>
</x-guest-layout>
