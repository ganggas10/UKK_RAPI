<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use App\Support\OtpRecipientPolicy;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthApiController extends Controller
{
    public function __construct(private readonly OtpRecipientPolicy $otpRecipientPolicy) {}

    /**
     * Login API - menghasilkan token Sanctum
     * Role yang bisa login via API: guru dan organisasi.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Siswa/tamu hanya menggunakan kalender publik tanpa login.
        if (! in_array($user->role, ['guru', 'organisasi'], true)) {
            return response()->json([
                'message' => 'Role tidak diizinkan akses mobile.',
            ], 403);
        }

        // Hapus token lama (opsional: satu device satu token)
        $user->tokens()->where('name', 'mobile')->delete();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Logout API - revoke current token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * Get current authenticated user info
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);
        $email = strtolower($validated['email']);
        $user = User::where('email', $email)->first();

        if ($user === null || ! $this->otpRecipientPolicy->isAllowed($email)) {
            return response()->json([
                'success' => true,
                'message' => 'Jika email terdaftar, kode OTP telah dikirim.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        try {
            $user->notify(new PasswordResetOtpNotification($otp));
        } catch (Throwable $exception) {
            Log::error('Password reset OTP email failed.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP gagal dikirim. Silakan coba lagi.',
            ], 500);
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

        return response()->json([
            'success' => true,
            'message' => 'Jika email terdaftar, kode OTP telah dikirim.',
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();
        $challenge = $user === null
            ? null
            : DB::table('password_reset_otps')->where('user_id', $user->id)->first();

        if (! $this->isValidOtp($challenge, $validated['otp'])) {
            $this->incrementOtpAttempts($challenge);

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP valid.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        $email = strtolower($validated['email']);

        $user = User::where('email', $email)->first();
        $challenge = $user === null
            ? null
            : DB::table('password_reset_otps')->where('user_id', $user->id)->first();

        if (! $this->isValidOtp($challenge, $validated['otp'])) {
            $this->incrementOtpAttempts($challenge);

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        DB::transaction(function () use ($user, $challenge, $validated): void {
            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(60),
            ])->save();

            DB::table('password_reset_otps')->where('id', $challenge->id)->delete();
            event(new PasswordReset($user));
        });

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ]);
    }

    private function isValidOtp(?object $challenge, string $otp): bool
    {
        return $challenge !== null
            && $challenge->attempts < 5
            && now()->lessThanOrEqualTo($challenge->expires_at)
            && Hash::check($otp, $challenge->otp_hash);
    }

    private function incrementOtpAttempts(?object $challenge): void
    {
        if ($challenge !== null && $challenge->attempts < 5) {
            DB::table('password_reset_otps')->where('id', $challenge->id)->increment('attempts');
        }
    }
}
