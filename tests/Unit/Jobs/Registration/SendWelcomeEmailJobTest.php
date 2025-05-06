<?php

namespace Tests\Unit\Jobs\Registration;

use App\Domain\Registration\Contracts\EmailNotificationServiceInterface;
use App\Jobs\Registration\SendWelcomeEmailJob;
use App\Mail\Registration\WelcomeEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use RuntimeException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SendWelcomeEmailJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_sends_welcome_email_and_marks_flag(): void
    {
        Mail::fake();
        $user = User::factory()->create(['welcome_email_sent_at' => null]);

        $job = new SendWelcomeEmailJob($user);
        $job->handle(app(EmailNotificationServiceInterface::class));

        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertNotNull($user->fresh()->welcome_email_sent_at);
    }

    #[Test]
    public function it_does_not_send_email_if_already_sent(): void
    {
        $user = User::factory()->create(['welcome_email_sent_at' => now()]);

        $emailService = $this->mock(EmailNotificationServiceInterface::class);
        $emailService->shouldNotReceive('sendWelcomeEmail');

        Log::shouldReceive('info')
            ->once()
            ->with("Welcome email already sent to user ID: {$user->id}");

        $job = new SendWelcomeEmailJob($user);
        $job->handle($emailService);
    }

    #[Test]
    public function it_logs_error_when_email_sending_fails(): void
    {
        $user = User::factory()->create(['welcome_email_sent_at' => null]);
        $exception = new RuntimeException('SMTP error');

        $emailService = $this->mock(EmailNotificationServiceInterface::class);
        $emailService->shouldReceive('sendWelcomeEmail')
            ->once()
            ->with($user)
            ->andThrow($exception);

        Log::shouldReceive('info')
            ->once()
            ->with("Sending welcome email to user ID: {$user->id}");
            
        Log::shouldReceive('error')
            ->once()
            ->with(
                'Error sending welcome email',
                [
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]
            );

        $this->expectExceptionObject($exception);
        
        $job = new SendWelcomeEmailJob($user);
        $job->handle($emailService);

        $this->assertNull($user->fresh()->welcome_email_sent_at);
    }
}