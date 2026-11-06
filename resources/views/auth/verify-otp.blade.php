<x-guest-layout>
    @section('title', 'Verifikasi OTP — RAPI-Sekolah')
    <h2 class="fw-bold mb-2">Verifikasi OTP</h2>
    <p class="text-muted mb-4">Masukkan kode 6 digit yang dikirim ke <strong>{{ $email }}</strong>.</p>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <form method="POST" action="{{ route('password.otp.verify') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <div class="mb-4">
            <label for="otp" class="form-label fw-semibold">Kode OTP</label>
            <input id="otp" class="form-control text-center fs-4 @error('otp') is-invalid @enderror" type="text" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus autocomplete="one-time-code">
            @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Verifikasi Kode</button>
    </form>
    <div class="text-center mt-3"><a href="{{ route('password.request') }}" class="text-muted small">Kirim kode baru</a></div>
</x-guest-layout>
