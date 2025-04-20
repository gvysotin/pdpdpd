<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\WelcomeEmailService;
//use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class SendWelcomeEmail
{
    public function __construct(
        private readonly WelcomeEmailService $mailer,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $this->logger->debug('Attempting to send welcome email', [
            'user_id' => $event->user->id,
            'email' => $event->user->email
        ]);
   
        try {
            $this->mailer->sendWelcomeEmail($event->user);
            $this->logger->info('Welcome email sent successfully', [
                'user_id' => $event->user->id,
                'email' => $event->user->email
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send welcome email', [
                'error' => $e->getMessage(),
                'user' => $event->user,
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
