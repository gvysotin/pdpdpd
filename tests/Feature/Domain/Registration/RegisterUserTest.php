<?php

namespace Tests\Feature\Domain\Registration;

use App\Domain\Registration\Contracts\EmailNotificationServiceInterface;
use App\Events\Registration\UserRegistered;
use App\Jobs\Registration\SendWelcomeEmailJob;
use App\Listeners\Registration\SendWelcomeEmailListener;
use App\Mail\Registration\WelcomeEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_registers_user_and_dispatches_event(): void
    {
        Event::fake([UserRegistered::class]);

        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $payload);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

        Event::assertDispatched(UserRegistered::class);
    }



    #[Test]
    public function it_sends_welcome_email_and_marks_flag(): void
    {
        // Создаём пользователя
        $user = User::factory()->create();

        // Мокируем сервис отправки email
        $emailService = Mockery::mock(EmailNotificationServiceInterface::class);
        $emailService->shouldReceive('sendWelcomeEmail')
            ->once()
            ->with($user)
            ->andReturn(true); // Явно указываем возвращаемое значение

        // Подменяем логгер для проверки
        Log::shouldReceive('info')
            ->with("Sending welcome email to user ID: {$user->id}");
        Log::shouldReceive('info')
            ->with("Welcome email sent and timestamp updated for user ID: {$user->id}");

        // Подключаем фейк очереди
        Queue::fake();

        // Создаём и выполняем джобу
        $job = new SendWelcomeEmailJob($user);
        $job->handle($emailService);

        // Убеждаемся, что:
        // 1. Email был отправлен (проверяется моком)
        // 2. Джоба не помещена в очередь (выполняется синхронно)
        Queue::assertNothingPushed();

        // Обновляем модель из базы
        $user->refresh();

        // Проверяем что поле обновилось
        $this->assertNotNull($user->welcome_email_sent_at);

        // Альтернативная проверка через базу данных
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'welcome_email_sent_at' => $user->welcome_email_sent_at,
        ]);
    }

    #[Test]
    public function it_does_not_send_email_if_already_sent(): void
    {
        $user = User::factory()->create(['welcome_email_sent_at' => now()]);

        $emailService = Mockery::mock(EmailNotificationServiceInterface::class);
        $emailService->shouldNotReceive('sendWelcomeEmail');

        Log::shouldReceive('info')
            ->with("Welcome email already sent to user ID: {$user->id}");

        $job = new SendWelcomeEmailJob($user);
        $job->handle($emailService);
    }

    #[Test]
    public function it_logs_error_when_email_sending_fails(): void
    {
        $user = User::factory()->create(['welcome_email_sent_at' => null]);

        $emailService = Mockery::mock(EmailNotificationServiceInterface::class);
        $emailService->shouldReceive('sendWelcomeEmail')
            ->once()
            ->andThrow(new RuntimeException('SMTP error'));

        // Мокируем ВСЕ ожидаемые вызовы логгера
        Log::shouldReceive('info')
            ->once()
            ->with("Sending welcome email to user ID: {$user->id}");

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($user) {
                return str_contains($message, 'Error sending welcome email') &&
                    $context['user_id'] == $user->id;
            });

        $this->expectException(RuntimeException::class);

        $job = new SendWelcomeEmailJob($user);
        $job->handle($emailService);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'welcome_email_sent_at' => null,
        ]);
    }

    #[Test]
    public function it_dispatches_welcome_email_job_from_event(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        event(new UserRegistered($user));

        Queue::assertPushed(SendWelcomeEmailJob::class, function ($job) use ($user) {
            return $job->getUser()->is($user); // используем геттер
        });
    }

 
    #[Test]
    public function test_listener_dispatches_job_with_correct_user(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        
        $event = new UserRegistered($user);

        (new SendWelcomeEmailListener())->handle($event);

        Queue::assertPushed(SendWelcomeEmailJob::class, function ($job) use ($user) {
            return $job->getUser()->is($user);
        });
    }

    #[Test]
    public function test_job_sends_welcome_email(): void
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


}
