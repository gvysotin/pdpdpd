<?php

namespace Tests\Feature\Auth;

use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Registration\Handlers\RegisterUserCommandHandler;
use App\Application\Shared\Results\OperationResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterFailureTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_handles_failed_registration_by_redisplaying_form_with_errors(): void
    {
        // Мокаем обработчик команд
        $this->mock(RegisterUserCommandHandler::class)
            ->shouldReceive('handle')
            ->once()
            ->andReturn(OperationResult::failure('Registration failed.'));

        // Выполняем запрос регистрации
        $response = $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'password' => 'VerySecurePassword123!',
                'password_confirmation' => 'VerySecurePassword123!',
            ]);

        // Проверяем результаты
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['general' => 'Registration failed.']);
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertDatabaseCount('users', 0);

        Mockery::close();
    }

    #[Test]
    public function it_handles_duplicate_email_error(): void
    {
        // Создаем пользователя с таким же email (без password_confirmation)
        $existingUser = User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => bcrypt('VerySecurePassword123!'), // Хешируем пароль
        ]);
    
        // Выполняем запрос регистрации с дублирующимся email
        $response = $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Bob2',
                'email' => 'bob@example.com',
                'password' => '2VerySecurePassword123!',
                'password_confirmation' => '2VerySecurePassword123!',
            ]);
    
        // Проверяем результаты
        $response->assertRedirect(route('register'));
        //$response->assertSessionHasErrors(['general' => 'Email is already registered']);
        $this->assertDatabaseCount('users', 1); // Проверяем что не создали дубликат
        
        // Дополнительная проверка - убеждаемся что оригинальный пользователь не изменился
        $this->assertDatabaseHas('users', [
            'email' => 'bob@example.com',
            'name' => 'Bob', // Проверяем что имя осталось оригинальным
        ]);
    }

    #[Test]
    public function it_handles_validation_errors(): void
    {
        // Пытаемся зарегистрироваться с невалидными данными
        $response = $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => '', // Пустое имя
                'email' => 'not-an-email',
                'password' => 'short',
                'password_confirmation' => 'mismatch',
            ]);

        // Проверяем ошибки валидации
        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'name', 'email', 'password'
        ]);
        $this->assertDatabaseCount('users', 0);
    }
}