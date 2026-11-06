<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function test_otp_is_sent_for_registered_user_even_if_email_is_unverified(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect(route('password.otp'))
            ->assertSessionHas('status');

        Notification::assertSentTo($user, PasswordResetOtpNotification::class);
    }

    public function test_unregistered_email_gets_generic_response(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'unknown@example.com'])
            ->assertRedirect(route('password.otp'))
            ->assertSessionHas('status')
            ->assertSessionDoesntHaveErrors();

        Notification::assertNothingSent();
    }

    public function test_sender_email_cannot_receive_its_own_reset_email(): void
    {
        Notification::fake();
        config(['mail.from.address' => 'ukkrapi@gmail.com']);
        User::factory()->create(['email' => 'ukkrapi@gmail.com']);

        $this->post('/forgot-password', ['email' => 'UKKRAPI@GMAIL.COM'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    public function test_a_different_recipient_can_receive_an_otp(): void
    {
        Notification::fake();
        config(['mail.from.address' => 'ukkrapi@gmail.com']);
        $user = User::factory()->create(['email' => 'recipient@example.com']);

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect(route('password.otp'))
            ->assertSessionHas('status');

        Notification::assertSentTo($user, PasswordResetOtpNotification::class);
    }

    public function test_expired_otp_can_be_sent_again(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        DB::table('password_reset_otps')->insert([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->subMinute(),
            'created_at' => now()->subMinutes(2),
            'updated_at' => now()->subMinutes(2),
        ]);

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect(route('password.otp'))
            ->assertSessionHas('status');

        Notification::assertSentTo($user, PasswordResetOtpNotification::class);
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'old-password']);

        $this->post('/forgot-password', ['email' => $user->email]);
        Notification::assertSentTo($user, PasswordResetOtpNotification::class, function (PasswordResetOtpNotification $notification) use ($user): bool {
            $this->post('/verify-reset-otp', [
                'email' => $user->email,
                'otp' => $notification->otp,
            ])->assertRedirect(route('password.reset'));

            $this->post('/reset-password', [
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])->assertRedirect(route('login'));

            return true;
        });

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
        $this->assertFalse(Hash::check('old-password', $user->refresh()->password));
    }

    public function test_invalid_otp_is_rejected(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);
        $this->post('/verify-reset-otp', [
            'email' => $user->email,
            'otp' => '000000',
        ])->assertSessionHasErrors('otp');
    }

    public function test_password_confirmation_must_match(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);
        Notification::assertSentTo($user, PasswordResetOtpNotification::class, function (PasswordResetOtpNotification $notification) use ($user): bool {
            $this->post('/verify-reset-otp', [
                'email' => $user->email,
                'otp' => $notification->otp,
            ]);

            $this->post('/reset-password', [
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ])->assertSessionHasErrors('password');

            return true;
        });
    }
}
