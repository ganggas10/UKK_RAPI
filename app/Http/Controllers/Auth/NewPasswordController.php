<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->isVerified($request)) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'email' => $request->session()->get('password_reset_verified_email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! $this->isVerified($request)) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Sesi reset password sudah berakhir. Silakan minta OTP baru.',
            ]);
        }

        $user = User::where('email', $request->session()->get('password_reset_verified_email'))->first();
        if ($user === null) {
            return redirect()->route('password.request');
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();
        event(new PasswordReset($user));
        $request->session()->forget([
            'password_reset_email',
            'password_reset_verified_email',
            'password_reset_verified_until',
        ]);

        return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan login dengan password baru Anda.');
    }

    private function isVerified(Request $request): bool
    {
        return $request->session()->has('password_reset_verified_email')
            && now()->timestamp <= (int) $request->session()->get('password_reset_verified_until');
    }
}
