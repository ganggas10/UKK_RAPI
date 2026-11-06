<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use App\Support\OtpRecipientPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    public function __construct(private readonly OtpRecipientPolicy $otpRecipientPolicy) {}

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);
        $email = strtolower($validated['email']);

        if (! $this->otpRecipientPolicy->isAllowed($email)) {
            return redirect()->route('password.request')->withInput()->withErrors([
                'email' => 'Email pengirim tidak boleh menjadi penerima email reset password.',
            ]);
        }

        $request->session()->put('password_reset_email', $email);
        $user = User::where('email', $email)->first();

        if ($user !== null) {
            $existingOtp = DB::table('password_reset_otps')->where('user_id', $user->id)->first();
            $otpAgeInSeconds = $existingOtp === null
                ? null
                : now()->timestamp - strtotime($existingOtp->created_at);

            if ($existingOtp === null || $otpAgeInSeconds >= 60) {
                $otp = (string) random_int(100000, 999999);

                try {
                    $user->notify(new PasswordResetOtpNotification($otp));
                } catch (Throwable $exception) {
                    Log::error('Password reset OTP email failed.', [
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                    ]);

                    return redirect()->route('password.request')->withInput()->withErrors([
                        'email' => 'Email OTP gagal dikirim. Silakan coba lagi atau hubungi administrator.',
                    ]);
                }

                DB::table('password_reset_otps')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'otp_hash' => Hash::make($otp),
                        'attempts' => 0,
                        'expires_at' => now()->addMinutes(10),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }

        return redirect()->route('password.otp')->with('status', 'Jika email tersebut terdaftar, kode OTP telah dikirim ke email Anda.');
    }

    public function showOtp(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', [
            'email' => $request->session()->get('password_reset_email'),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);
        $email = strtolower($validated['email']);

        if ($email !== $request->session()->get('password_reset_email')) {
            return back()->withErrors(['otp' => 'Sesi verifikasi OTP tidak valid.']);
        }

        $userId = User::where('email', $email)->value('id');
        $challenge = DB::table('password_reset_otps')->where('user_id', $userId)->first();

        if ($challenge === null || now()->greaterThan($challenge->expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.']);
        }
        if ($challenge->attempts >= 5) {
            return back()->withErrors(['otp' => 'Batas percobaan OTP telah tercapai. Silakan minta kode baru.']);
        }
        if (! Hash::check($validated['otp'], $challenge->otp_hash)) {
            DB::table('password_reset_otps')->where('id', $challenge->id)->increment('attempts');

            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.']);
        }

        DB::table('password_reset_otps')->where('id', $challenge->id)->delete();
        $request->session()->put('password_reset_verified_email', $email);
        $request->session()->put('password_reset_verified_until', now()->addMinutes(10)->timestamp);

        return redirect()->route('password.reset');
    }
}
