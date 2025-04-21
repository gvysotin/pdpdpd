<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\WelcomeEmailService;
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
        $user = $event->user;

        // Проверка: уже отправляли?
        if ($user->welcome_email_sent_at !== null) {
            $this->logger->info('Welcome email already sent, skipping.', [
                'user_id' => $user->id,
                'email_hash' => hash('sha256', $user->email)               
            ]);
            return;
        }
   
        try {
            $this->mailer->send($user);

            // Отметим, что письмо отправлено
            $user->update([
                'welcome_email_sent_at' => now(),
            ]);

            $this->logger->info('Welcome email sent successfully.', [
                'user_id' => $user->id,
                'email_hash' => hash('sha256', $user->email),            
                'welcome_email_sent_at' => now()->toIso8601String() // Таймстамп                   
            ]);

        } catch (Throwable $e) {
            $this->logger->error('Failed to send welcome email', [
                'error' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(), // Полный трейс                
                'user' => [
                    'id' => $user->id,
                    'email' => hash('sha256', $user->email)
                ],            
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
