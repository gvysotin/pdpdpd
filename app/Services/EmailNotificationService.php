<?php

namespace App\Services;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Services\Interfaces\EmailNotificationServiceInterface;

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