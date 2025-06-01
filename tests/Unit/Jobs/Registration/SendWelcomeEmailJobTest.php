<?php

namespace Tests\Unit\Jobs\Registration;

use App\Domain\Registration\Contracts\EmailNotificationServiceInterface;
use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\Contracts\UserRepositoryInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Exceptions\UserRegistrationException;
use App\Domain\Registration\Services\UserCreator;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
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
        // 1. Создаем пользователя с уже отправленным email
        $user = User::factory()->create(['welcome_email_sent_at' => now()]);

        // 2. Создаем мок сервиса email
        $emailService = Mockery::mock(EmailNotificationServiceInterface::class);

        // 3. Утверждаем, что метод sendWelcomeEmail НЕ должен быть вызван
        $emailService->shouldNotReceive('sendWelcomeEmail');

        // 4. Ожидаем запись в лог
        Log::shouldReceive('info')
            ->once() // Добавляем проверку количества вызовов
            ->with("Welcome email already sent to user ID: {$user->id}")
            ->andReturnNull();

        // 5. Создаем и выполняем job
        $job = new SendWelcomeEmailJob($user);
        $job->handle($emailService);

        // 6. Проверяем, что дата отправки не изменилась
        $this->assertNotNull($user->fresh()->welcome_email_sent_at);

        // 7. Закрываем моки (необязательно, Laravel делает это автоматически)
        Mockery::close();
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

    #[Test]
    public function it_throws_exception_when_email_exists(): void
    {
        $userFactory = Mockery::mock(UserFactoryInterface::class);
        $spec = Mockery::mock(EmailSpecificationInterface::class);
        $userRepository = Mockery::mock(UserRepositoryInterface::class);

        $spec->shouldReceive('check')
            ->once()
            ->andThrow(new UserRegistrationException('Email exists'));

        $this->expectException(UserRegistrationException::class);

        $creator = new UserCreator(
            $userFactory,
            $spec,
            $userRepository
        );

        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('password123')
        );

        $creator->create($dto);

        Mockery::close();
    }

    #[Test]
    public function it_throws_exception_when_save_fails(): void
    {
        // 1. Создаем моки для всех зависимостей
        $userFactory = Mockery::mock(UserFactoryInterface::class);
        $spec = Mockery::mock(EmailSpecificationInterface::class);
        $userRepository = Mockery::mock(UserRepositoryInterface::class);

        // 2. Настраиваем ожидание проверки email (это было пропущено)
        $spec->shouldReceive('check')
            ->once()
            ->andReturnNull(); // или with() если нужно проверить параметры

        // 3. Настраиваем ожидание создания пользователя
        $userMock = Mockery::mock(User::class);
        $userFactory->shouldReceive('createFromDTO')
            ->once()
            ->andReturn($userMock);

        // 4. Настраиваем ожидание ошибки при сохранении
        $userRepository->shouldReceive('save')
            ->once()
            ->with($userMock)
            ->andThrow(new RuntimeException('Database error'));

        // 5. Ожидаем исключение
        $this->expectException(UserRegistrationException::class);
        $this->expectExceptionMessage('Could not save user');

        // 6. Создаем тестируемый объект
        $creator = new UserCreator(
            $userFactory,
            $spec,
            $userRepository
        );

        // 7. Подготавливаем тестовые данные
        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('password123')
        );

        // 8. Вызываем тестируемый метод
        $creator->create($dto);

        // 9. Закрываем моки (необязательно, Laravel делает это автоматически)
        Mockery::close();        
    }

}