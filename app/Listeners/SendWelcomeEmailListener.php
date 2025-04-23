<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Events\UserRegistered;
use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\EmailNotificationServiceInterface;


class SendWelcomeEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $backoff = 10;
    public $timeout = 30;

    /**
     * Create the event listener.
     */
    public function __construct(
        private EmailNotificationServiceInterface $emailService        
    )
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        if ($user->hasReceivedWelcomeEmail()) {
            Log::info("Welcome email already sent to user ID: {$user->id}");
            return;
        }

        Log::info("Sending welcome email to user ID: {$user->id}");

        $this->emailService->sendWelcomeEmail($user);

        $user->markWelcomeEmailAsSent();        

        Log::info("Welcome email sent and timestamp updated for user ID: {$user->id}");
    }
}
