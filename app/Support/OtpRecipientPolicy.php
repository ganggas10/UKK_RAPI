<?php

namespace App\Support;

class OtpRecipientPolicy
{
    public function isAllowed(string $recipient): bool
    {
        $sender = strtolower(trim((string) config('mail.from.address')));

        return strtolower(trim($recipient)) !== $sender;
    }
}
