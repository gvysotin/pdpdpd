<?php

namespace Tests\Feature\Application\Registration\Handlers;

use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Registration\Handlers\RegisterUserCommandHandler;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\{Email, PlainPassword};
use App\Events\Registration\UserRegistered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Exception;
use Mockery;
use DB;


class RegisterUserCommandHandlerTest extends TestCase
{
    //use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    
        // Очистить вручную
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');        
    }

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
        // 1. Сначала выполним команду для создания пользователя
        $firstDto = new UserRegistrationData(
            name: 'First User',
            email: new Email('existing@example.com'),
            password: new PlainPassword('password123')
        );

        $handler = $this->app->make(RegisterUserCommandHandler::class);
        $firstResult = $handler->handle(new RegisterUserCommand($firstDto));

        // 2. Проверим, что первый пользователь создан успешно
        $this->assertTrue($firstResult->succeeded());
        $this->assertDatabaseCount('users', 1);

        // $user = User::first();
        // dump('First user is:');
        // dump($user);

        // 3. Теперь попробуем создать дубликат
        $secondDto = new UserRegistrationData(
            name: 'Second User',
            email: new Email('existing@example.com'),
            password: new PlainPassword('password123')
        );

        $secondResult = $handler->handle(new RegisterUserCommand($secondDto));

        // 4. Проверяем, что вторая попытка провалилась
        $this->assertTrue($secondResult->failed());
        $this->assertEquals('Email already registered', $secondResult->message());

        // $user = User::first();
        // dump('First user is:');
        // dump($user);

        $this->assertDatabaseCount('users', 1); // Все еще только один пользователь
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
    }

}