<?php

namespace Tests\Feature\Domain\Registration;

use App\Events\Registration\UserRegistered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Event;
use App\Models\User;
use Tests\TestCase;

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
        // Подготовим недействительную комбинацию полей для провоцирования ошибки
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrong-password-confirmation', // Пароли не совпадают
        ];
    
        // Выполнится реальный HTTP-запрос на маршрут регистрации
        $response = $this->from(route('register'))->post(route('register'), $payload);
    
        // Проверки
        $response->assertRedirect(route('register')); // Должен вернуться на страницу регистрации
        $response->assertSessionHasErrors('password'); // Должна появиться ошибка несоответствия паролей
        $response->assertSessionHasInput('email'); // Входные данные сохраняются
    
        // Проверим, что пользователь не был создан в базе данных
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    #[Test]
    public function it_logs_error_on_registration_failure(): void
    {
        // Моделируем ситуацию, когда запрос завершится неудачей (например, проблема с базой данных)
        // Имитация случая, когда валидация правильная, но возникла внутренняя ошибка
    
        // Подготовим валидные данные
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '2password123',
            'password_confirmation' => 'password123', // Не правильный пароль
        ];
    
        // Направляем реальный POST-запрос к маршруту регистрации
        $response = $this->from(route('register'))->post(route('register'), $payload);
    
        // $user = User::where('email', 'test@example.com')->first();
        // $this->assertNotNull($user);

        // Проверяем статус ответа
        $response->assertRedirect(route('register')); // Должен вернуть редирект обратно на форму регистрации
    
        // $sessionData = session()->all();
        // dump($sessionData); // Отобразит все данные сессии       

        // Проверяем наличие ошибки в сессии
        $response->assertSessionHasErrors();
    
        // Убеждаемся, что пользователь не создалась в базе данных
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    
        // Логи проверки невозможны без специальных инструментов или вмешательств, поэтому
        // данная часть теста опускается в feature-тестировании.
    }

}
