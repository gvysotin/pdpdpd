<?php

namespace Tests\Feature\Application\Registration\Handlers;

use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Registration\Handlers\RegisterUserCommandHandler;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use App\Events\Registration\UserRegistered;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterUserCommandHandlerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_successfully_registers_user_with_real_dependencies(): void
    {
        Event::fake();
        Log::spy();

        // Подготовка тестовых данных
        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('password123')
        );

        $command = new RegisterUserCommand($dto);

        // Создаем обработчик с реальными зависимостями из контейнера
        $handler = $this->app->make(RegisterUserCommandHandler::class);

        // Выполняем команду
        $result = $handler->handle($command);

        // Проверяем результаты
        $this->assertTrue($result->succeeded());
        
        // Проверяем, что пользователь создан в БД
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        // Проверяем, что пароль хеширован
        $user = User::first();
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(password_verify('password123', $user->password));

        // Проверяем, что событие было отправлено
        Event::assertDispatched(UserRegistered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

        // Проверяем логи
        Log::shouldHaveReceived('info')
            ->with('Starting user registration', Mockery::any())
            ->once();
            
        Log::shouldHaveReceived('info')
            ->with('New user registered', Mockery::any())
            ->once();
    }

    #[Test]
    public function it_fails_when_email_already_registered(): void
    {
        // Создаем существующего пользователя
        User::factory()->create(['email' => 'existing@example.com']);

        // Подготовка тестовых данных
        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('existing@example.com'),
            password: new PlainPassword('password123')
        );

        $command = new RegisterUserCommand($dto);
        $handler = $this->app->make(RegisterUserCommandHandler::class);

        // Выполняем команду
        $result = $handler->handle($command);

        // Проверяем результаты
        $this->assertTrue($result->failed());
        $this->assertEquals('Email already registered', $result->message());
        $this->assertDatabaseCount('users', 0); // Проверяем, что нового пользователя не создали
    }

    #[Test]
    public function it_fails_gracefully_on_database_error(): void
    {
        Event::fake();
        Log::spy();

        // Подготовка тестовых данных
        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('password123')
        );

        $command = new RegisterUserCommand($dto);

        // Создаем обработчик с моком репозитория, который выбросит исключение
        $mockUserCreator = $this->mock(UserCreatorInterface::class);
        $mockUserCreator->shouldReceive('create')
            ->andThrow(new Exception('Database connection failed'));

        $handler = new RegisterUserCommandHandler(
            $mockUserCreator,
            $this->app->make(EmailSpecificationInterface::class),
            Log::getFacadeRoot(),
            $this->app->make(TransactionManagerInterface::class)
        );

        // Выполняем команду
        $result = $handler->handle($command);

        // Проверяем результаты
        $this->assertTrue($result->failed());
        $this->assertEquals('Failed to register user', $result->message());
        $this->assertDatabaseCount('users', 0);
        Event::assertNotDispatched(UserRegistered::class);

        Mockery::close();        
    }
}