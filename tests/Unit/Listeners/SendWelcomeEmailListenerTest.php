<?php

namespace Tests\Unit\Listeners;

use App\Domain\Registration\Contracts\EmailNotificationServiceInterface;
use App\Events\Registration\UserRegistered;
use App\Listeners\Registration\SendWelcomeEmailListener;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Mockery;

class SendWelcomeEmailListenerTest extends TestCase
{
    #[Test]
    public function it_sends_welcome_email_when_not_sent_yet(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('welcome_email_sent_at')->andReturn(null);
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');

        $event = new UserRegistered($user);

        $emailService = Mockery::mock(EmailNotificationServiceInterface::class);
        $emailService->shouldReceive('sendWelcomeEmail')->once()->with($user);

        $listener = new SendWelcomeEmailListener($emailService);
        $listener->handle($event);
    }

    #[Test]
    public function it_does_not_send_email_if_already_sent(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('welcome_email_sent_at')->andReturn(now());

        $event = new UserRegistered($user);

        $emailService = Mockery::mock(EmailNotificationServiceInterface::class);
        $emailService->shouldNotReceive('sendWelcomeEmail');

        $listener = new SendWelcomeEmailListener($emailService);
        $listener->handle($event);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}