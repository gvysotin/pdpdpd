<?php

namespace Tests\Feature\Auth;

use App\Application\Registration\Actions\RegisterUserAction;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\HashedPassword;
use App\Domain\Registration\ValueObjects\PlainPassword;
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
        // Подготавливаем DTO с хешированным паролем
        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new HashedPassword(bcrypt('password123')) // Используем шифрование
        );

        // Создаем экземпляр реального действия регистрации
        $action = resolve(RegisterUserAction::class);

        // Выполняем регистрацию
        $result = $action->execute($dto);

        // Проверяем успешность регистрации
        $this->assertTrue($result->succeeded());

        // Проверяем, что пользователь появился в базе данных
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}