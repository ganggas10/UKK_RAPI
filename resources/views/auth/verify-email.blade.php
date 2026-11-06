<x-guest-layout>
    @section('title', 'Verifikasi Email — RAPI-Sekolah')
    <h2 class="fw-bold mb-2">Verifikasi Email</h2>
    <div class="mb-4 text-muted">
        Link verifikasi sudah dikirim ke email Anda. Klik link tersebut agar akun dapat digunakan untuk reset password dengan OTP.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <button type="submit" class="btn btn-primary">Kirim Ulang Email Verifikasi</button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn btn-link text-muted">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
