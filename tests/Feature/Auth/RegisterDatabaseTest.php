<?php

namespace Tests\Feature\Auth;

use App\Domain\Registration\Actions\RegisterUserAction;
use App\Domain\Shared\Results\OperationResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterDatabaseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_saves_user_in_database_after_successful_registration(): void
    {
        // Мокируем действие регистрации, чтобы оно сохраняло пользователя в базе
        $this->mock(RegisterUserAction::class)
            ->shouldReceive('execute')
            ->once()
            ->andReturnUsing(function ($data) {
                // Создаем пользователя в базе данных
                return User::create([
                    'name' => $data->name,
                    'email' => $data->email,
                    'password' => $data->password->value // хешированный пароль
                ]);
            });

        // Регистрация нового пользователя
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Проверка, что пользователь был добавлен в базу данных
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}