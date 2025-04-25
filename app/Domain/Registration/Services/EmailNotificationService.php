<?php

namespace App\Domain\Registration\Services;

use App\Domain\Registration\Contracts\EmailNotificationServiceInterface;
use App\Mail\Registration\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService implements EmailNotificationServiceInterface
{
    public function sendWelcomeEmail(User $user): void
    {
        Mail::to($user->email)->send(new WelcomeEmail($user));
    }

    public function sendVerificationEmail(User $user): void
    {
        $user->sendEmailVerificationNotification();
    }
}