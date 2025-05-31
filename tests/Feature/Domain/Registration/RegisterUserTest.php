<?php

namespace Tests\Feature\Domain\Registration;

use App\Application\Registration\Actions\RegisterUserAction;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Shared\Results\OperationResult;
use App\Events\Registration\UserRegistered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_registers_user_successfully(): void
    {
        Event::fake([UserRegistered::class]);

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    #[Test]
    public function it_dispatches_user_registered_event(): void
    {
        Event::fake([UserRegistered::class]);

        $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        Event::assertDispatched(UserRegistered::class, fn($e) => $e->user->is($user));
    }

    #[Test]
    public function it_handles_registration_failure_gracefully(): void
    {

        // Подготовка фейковых данных запроса
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Мокаем действие регистрации
        $mockedAction = Mockery::mock(RegisterUserAction::class);
        $mockedAction
            ->shouldReceive('execute')
            ->once()
            ->with(Mockery::type(UserRegistrationData::class)) // Проверяем тип DTO
            ->andReturn(OperationResult::failure('Something went wrong'));

        // Подменяем реализацию в контейнере
        $this->app->instance(RegisterUserAction::class, $mockedAction);

        // Выполняем запрос
        $response = $this->from(route('register')) // Важно указать from для back()
            ->post(route('register'), $payload);

        // Проверки
        $response->assertRedirect(route('register')); // Явно указываем куда
        $response->assertSessionHasErrors(['general' => 'Something went wrong']); // Проверяем errors, а не error
        $response->assertSessionHasInput('email'); // Проверяем сохранение ввода

        // Убедимся, что пользователь не создан
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);

        Mockery::close();
    }

    #[Test]
    public function it_logs_error_on_registration_failure(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->once();
        $logger->shouldReceive('error')
            ->once()
            ->with('User registration failed', Mockery::on(function ($context) {
                return isset($context['exception']) && $context['source'] === 'web';
            }));
    
        $userCreator = Mockery::mock(UserCreatorInterface::class);
        $userCreator->shouldReceive('create')
            ->andThrow(new RuntimeException('Database error'));
    
        $this->app->instance(UserCreatorInterface::class, $userCreator);
        $this->app->instance(LoggerInterface::class, $logger);
    
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
    
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['general' => 'Failed to register user']);
    
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);

        Mockery::close();
    }

}
