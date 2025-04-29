<?php

namespace App\Jobs\Registration;

use Throwable;
use App\Models\User;
use App\Domain\Registration\Contracts\EmailNotificationServiceInterface;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWelcomeEmailJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 10;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $user
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(EmailNotificationServiceInterface $emailService): void
    {
        if ($this->user->hasReceivedWelcomeEmail()) {
            Log::info("Welcome email already sent to user ID: {$this->user->id}");
            return;
        }

        Log::info("Sending welcome email to user ID: {$this->user->id}");

        try {
            $emailService->sendWelcomeEmail($this->user);
            $this->user->markWelcomeEmailAsSent();

            Log::info("Welcome email sent and timestamp updated for user ID: {$this->user->id}");
        } catch (Throwable $e) {
            Log::error('Error sending welcome email', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Laravel сам повторит Job
        }        

    }

    public function getUser(): User
    {
        return $this->user;
    }

}
