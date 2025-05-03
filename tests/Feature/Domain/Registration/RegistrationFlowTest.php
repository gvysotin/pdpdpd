<?php

namespace Tests\Feature\Domain\Registration;


use App\Domain\Registration\Actions\RegisterUserAction;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use App\Events\Registration\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Инициализация моков/шпионов
        Event::fake();
        Log::spy();
    }

    #[Test]
    public function it_successfully_registers_user_with_all_side_effects()
    {
        // 1. Подготовка тестовых данных
        $registrationData = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('password123')
        );

        // 2. Выполнение действия
        $action = app(RegisterUserAction::class);
        $result = $action->execute($registrationData);

        // 3. Проверки
        $this->assertTrue($result->succeeded());

        // Проверка записи в БД
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $user = User::firstWhere('email', 'test@example.com');

        // Проверка хеширования пароля
        $this->assertTrue(password_verify('password123', $user->password));

        // Проверка события (имитация afterCommit)
        Event::assertDispatched(UserRegistered::class, fn($e) => $e->user->is($user));

        // Проверка логов

        Log::shouldHaveReceived('info')
            ->with('Starting user registration', Mockery::subset([
                'email_hash' => hash('sha256', 'test@example.com')
            ]))
            ->with('New user registered', Mockery::subset([
                'event_dispatched' => true
            ]));

        // Проверка, что ошибок не логировалось
        Log::shouldNotHaveReceived('error');

    }


    #[Test]
    public function it_fails_gracefully_on_user_creator_exception(): void
    {
        // Подменяем реализацию UserCreatorInterface на мок, выбрасывающий исключение
        $mock = Mockery::mock(UserCreatorInterface::class);
        $mock->shouldReceive('create')
            ->andThrow(new RuntimeException('DB write failed'));
    
        $this->app->instance(UserCreatorInterface::class, $mock);
    
        Event::fake();
        Log::spy();
    
        $registrationData = new UserRegistrationData(
            name: 'Broken User',
            email: new Email('fail@example.com'),
            password: new PlainPassword('brokenpass')
        );
    
        $action = app(RegisterUserAction::class);
        $result = $action->execute($registrationData);
    
        // Проверка результата — действие завершилось неудачей
        $this->assertFalse($result->succeeded());
        $this->assertSame('Failed to register user', $result->message());
    
        // Проверка, что пользователь не был создан в базе данных
        $this->assertDatabaseMissing('users', [
            'email' => 'fail@example.com',
        ]);
    
        // Проверка, что событие регистрации не было отправлено
        Event::assertNotDispatched(UserRegistered::class);
    
        // Проверка, что лог ошибки был записан
        Log::shouldHaveReceived('error')
            ->with('User registration failed', Mockery::on(function ($context) {
                return isset($context['exception']) &&
                       $context['exception'] instanceof RuntimeException &&
                       $context['exception']->getMessage() === 'DB write failed';
            }));
    
        // Проверка, что лог "New user registered" не записывался
        Log::assertNotLogged('info', function ($message, $context) {
            return str_contains($message, 'New user registered');
        });
    }


    #[Test]
    public function it_logs_error_when_registration_fails()
    {
        // 1. Мокируем зависимости
        $userCreatorMock = Mockery::mock(UserCreatorInterface::class);
        $userCreatorMock->shouldReceive('create')
            ->with(Mockery::type(UserRegistrationData::class))
            ->andThrow(new RuntimeException('DB failed'));
        
        $this->app->instance(UserCreatorInterface::class, $userCreatorMock);
    
        // 2. Мокируем логгер
        $loggerMock = Mockery::mock(LoggerInterface::class);
        $loggerMock->shouldReceive('info'); // для start логирования
        $loggerMock->shouldReceive('error')
            ->once()
            ->withArgs(function($message, $context) {
                return $message === 'User registration failed'
                    && isset($context['exception'])
                    && $context['exception'] instanceof RuntimeException
                    && $context['exception']->getMessage() === 'DB failed'
                    && $context['email_hash'] === hash('sha256', 'bad@example.com')
                    && $context['source'] === 'web';
            });
        
        $this->app->instance(LoggerInterface::class, $loggerMock);
    
        // 3. Подготавливаем данные
        $data = new UserRegistrationData(
            name: 'Bad User',
            email: new Email('bad@example.com'),
            password: new PlainPassword('validPass123')
        );
    
        // 4. Выполняем
        $action = app(RegisterUserAction::class);
        $result = $action->execute($data);
    
        // 5. Проверяем
        $this->assertFalse($result->succeeded());
        $this->assertDatabaseMissing('users', ['email' => 'bad@example.com']);
    }


    #[Test]
    public function it_handles_registration_failure_properly()
    {
        // Подготовка мока с ошибкой
        $this->mock(UserCreatorInterface::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new RuntimeException('DB error'));
        });

        $action = app(RegisterUserAction::class);
        $result = $action->execute(new UserRegistrationData(
            name: 'Test',
            email: new Email('fail@example.com'),
            password: new PlainPassword('password123')
        ));

        // Проверки
        $this->assertTrue($result->failed());
        $this->assertDatabaseMissing('users', ['email' => 'fail@example.com']);
        Event::assertNotDispatched(UserRegistered::class);

        Log::shouldHaveReceived('error')
            ->with('User registration failed', Mockery::hasKey('exception'));
    }
}
