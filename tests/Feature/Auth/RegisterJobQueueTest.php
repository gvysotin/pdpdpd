<?php

namespace Tests\Feature\Auth;

use App\Domain\Registration\Contracts\EmailNotificationServiceInterface;
use App\Events\Registration\UserRegistered;
use App\Jobs\Registration\SendWelcomeEmailJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use Tests\TestCase;
use Mockery;


final class RegisterJobQueueTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_welcome_email_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        // Эмулируем событие регистрации пользователя
        event(new UserRegistered($user));

        // Проверка, что джоба на отправку письма помещена в очередь
        Queue::assertPushed(SendWelcomeEmailJob::class, function ($job) use ($user) {
            return $job->getUser()->is($user); // Проверяем, что пользователь правильный
        });
    }

    #[Test]
    public function it_sends_welcome_email_and_marks_flag(): void
    {
        $user = User::factory()->create();

        // Мокируем email-сервис
        $emailService = Mockery::mock(EmailNotificationServiceInterface::class);

        $emailService->shouldReceive('sendWelcomeEmail')
            ->once()
            ->with($user); // Указываем, что email был отправлен

        Queue::fake(); // Фейк очереди для выполнения синхронно

        // Создаем и выполняем Job
        $job = new SendWelcomeEmailJob($user);
        $job->handle($emailService);

        Queue::assertNothingPushed(); // Проверяем, что Job не был помещен в очередь, а выполнен синхронно

        $user->refresh();
        $this->assertNotNull($user->welcome_email_sent_at); // Проверка обновленного поля в базе данных

    }


}