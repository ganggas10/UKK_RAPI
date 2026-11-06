<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthApiPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_an_otp_and_returns_json(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Jika email terdaftar, kode OTP telah dikirim.',
            ]);

        Notification::assertSentTo($user, PasswordResetOtpNotification::class);
        $this->assertDatabaseHas('password_reset_otps', ['user_id' => $user->id]);
    }

    public function test_verify_otp_accepts_a_valid_code_and_rejects_an_expired_code(): void
    {
        $user = User::factory()->create();
        DB::table('password_reset_otps')->insert([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/verify-otp', [
            'email' => $user->email,
            'otp' => '123456',
        ])->assertOk()->assertJson([
            'success' => true,
            'message' => 'Kode OTP valid.',
        ]);

        DB::table('password_reset_otps')->where('user_id', $user->id)->update([
            'expires_at' => now()->subMinute(),
        ]);

        $this->postJson('/api/verify-otp', [
            'email' => $user->email,
            'otp' => '123456',
        ])->assertStatus(422)->assertJson([
            'success' => false,
            'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa.',
        ]);
    }

    public function test_reset_password_updates_the_password_and_deletes_the_otp(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        DB::table('password_reset_otps')->insert([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk()->assertJson([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ]);

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
        $this->assertDatabaseMissing('password_reset_otps', ['user_id' => $user->id]);
    }

    public function test_password_reset_api_validates_required_fields(): void
    {
        $this->postJson('/api/reset-password')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'otp', 'password']);
    }
}
