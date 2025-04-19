<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\EmailNotificationService;
use Illuminate\Support\Facades\Log;


class SendWelcomeEmail
{
    protected EmailNotificationService $mailer;

    /**
     * Create the event listener.
     */
    public function __construct(EmailNotificationService $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $this->mailer->sendWelcomeEmail($event->user);
        Log::info('SendWelcomeEmail listener отработал', ['user' => $event->user]);
    }
}
