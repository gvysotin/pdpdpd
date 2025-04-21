<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Jobs\SendWelcomeEmailJob;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        // Асинхронно отправляем письмо и обновим флаг welcome_email_sent_at
        SendWelcomeEmailJob::dispatch($event->user);
    }
}

