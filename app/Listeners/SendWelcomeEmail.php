<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\WelcomeEmailService;
//use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Throwable;

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
            'email' => $event->user->email,
            'ip' => request()->ip() // Важно для безопасности
        ]);
   
        try {
            $this->mailer->send($event->user);
            $this->logger->info('Welcome email sent successfully', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,                
                'email_sent_at' => now()->toIso8601String() // Таймстамп                
            ]);
        } catch (Throwable $e) {
            $this->logger->error('Failed to send welcome email', [
                'error' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(), // Полный трейс                
                'user' => [
                    'id' => $event->user->id,
                    'email' => $event->user->email
                ],            
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
