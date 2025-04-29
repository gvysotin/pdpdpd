<?php

namespace Tests\Feature\Domain\Registration;

use App\Domain\Registration\Contracts\EmailNotificationServiceInterface;
use App\Events\Registration\UserRegistered;
use App\Jobs\Registration\SendWelcomeEmailJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
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
    public function it_sends_welcome_email_and_marks_flag(): void
    {
        // Создаём пользователя с пустым полем welcome_email_sent_at
        $user = User::factory()->create([
            'welcome_email_sent_at' => null,
        ]);
    
        // Мокируем сервис отправки email
        $emailService = Mockery::mock(EmailNotificationServiceInterface::class);
        $emailService->shouldReceive('sendWelcomeEmail')
            ->once()
            ->with($user)
            ->andReturn(true); // Явно указываем возвращаемое значение
    
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

}
