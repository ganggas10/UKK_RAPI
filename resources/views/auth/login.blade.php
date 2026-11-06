<x-guest-layout>
    @section('title', 'Login — RAPI-Sekolah')

    <form method="POST" action="{{ route('login') }}">
        @csrf

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" autofocus autocomplete="username">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <input id="login-password" type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       autocomplete="current-password">
                <button type="button" class="btn btn-outline-secondary" id="toggle-login-password"
                        aria-label="Tampilkan password" aria-pressed="false">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" name="remember" id="remember">
            <label class="form-check-label text-muted" for="remember">Ingat saya</label>
        </div>

        <div class="text-end mb-3">
            <a href="{{ route('password.request') }}" class="small text-primary">Lupa Password?</a>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">Masuk ke Sistem</button>
        </div>

        <div class="mt-3 p-2 rounded bg-light border text-center small text-muted">
            <i class="bi bi-info-circle text-primary me-1"></i>
            Dapat digunakan oleh <strong>Waka/Admin</strong>, <strong>PIC Ruang</strong>, serta <strong>Guru &amp; Organisasi</strong> (terutama pengguna iOS/Web).
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('calendar.public') }}" class="text-muted small fw-semibold text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda &amp; Kalender Publik
            </a>
        </div>
    </form>

    <script>
        document.getElementById('toggle-login-password')?.addEventListener('click', function () {
            const password = document.getElementById('login-password');
            const icon = this.querySelector('i');
            const isVisible = password.type === 'text';

            password.type = isVisible ? 'password' : 'text';
            icon.classList.toggle('bi-eye', isVisible);
            icon.classList.toggle('bi-eye-slash', !isVisible);
            this.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
            this.setAttribute('aria-pressed', String(!isVisible));
        });
    </script>
</x-guest-layout>