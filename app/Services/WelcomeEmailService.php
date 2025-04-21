<?php

// app/Services/WelcomeEmailService.php
namespace App\Services;

use App\Contracts\WelcomeEmailSenderInterface;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailer;

class WelcomeEmailService implements WelcomeEmailSenderInterface
{
    public function __construct(
        private Mailer $mailer
    ) {}

    public function send(User $user): void
    {
        $this->mailer->to($user->email)->send(new WelcomeEmail($user));

        // Отметим, что письмо отправлено
        $user->update([
            'welcome_email_sent_at' => now(),
        ]);
    }

}