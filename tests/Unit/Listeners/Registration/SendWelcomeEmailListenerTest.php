<?php

namespace Tests\Unit\Listeners\Registration;

use App\Events\Registration\UserRegistered;
use App\Jobs\Registration\SendWelcomeEmailJob;
use App\Listeners\Registration\SendWelcomeEmailListener;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Queue;
use App\Models\User;
use Tests\TestCase;

class SendWelcomeEmailListenerTest extends TestCase
{
    #[Test]
    public function it_dispatches_welcome_email_job(): void
    {
        Queue::fake();
        $user = User::factory()->make(); // Используем make() вместо create()

        $listener = new SendWelcomeEmailListener();
        $listener->handle(new UserRegistered($user));

        Queue::assertPushed(SendWelcomeEmailJob::class, function ($job) use ($user) {
            return $job->getUser()->is($user);
        });
    }
}