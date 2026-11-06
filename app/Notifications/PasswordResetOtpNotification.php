<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification
{
    use Queueable;

    public function __construct(public string $otp) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode OTP Reset Password RAPI-Sekolah')
            ->greeting('Halo, '.$notifiable->name)
            ->line('Gunakan kode OTP berikut untuk mereset password akun RAPI-Sekolah Anda:')
            ->line('**'.$this->otp.'**')
            ->line('Kode ini berlaku selama 10 menit dan maksimal 5 kali percobaan.')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
