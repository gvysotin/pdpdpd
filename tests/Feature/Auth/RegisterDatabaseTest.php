<?php

namespace Tests\Feature\Auth;

use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Registration\Handlers\RegisterUserCommandHandler;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\{Email, PlainPassword};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterDatabaseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_saves_user_in_database_after_successful_registration(): void
    {
        // Подготавливаем DTO с обычным паролем (хеширование произойдет в обработчике)
        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('password123')
        );

        // Получаем обработчик команд из контейнера
        $handler = app(RegisterUserCommandHandler::class);

        // Создаем и выполняем команду
        $command = new RegisterUserCommand($dto);
        $result = $handler->handle($command);

        // Проверяем успешность регистрации
        $this->assertTrue($result->succeeded());

        // Проверяем, что пользователь появился в базе данных
        $this->assertDatabaseHas(User::class, [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Дополнительная проверка хеширования пароля
        $user = User::first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    #[Test]
    public function it_hashes_password_correctly(): void
    {
        $dto = new UserRegistrationData(
            name: 'Jane Doe',
            email: new Email('jane@example.com'),
            password: new PlainPassword('secure123')
        );

        $handler = app(RegisterUserCommandHandler::class);
        $handler->handle(new RegisterUserCommand($dto));

        $user = User::first();
        $this->assertNotEquals('secure123', $user->password);
        $this->assertTrue(Hash::check('secure123', $user->password));
    }

    #[Test]
    public function it_rolls_back_transaction_on_failure(): void
    {
        // Создаем пользователя с таким же email
        User::factory()->create(['email' => 'existing@example.com']);

        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('existing@example.com'), // Дублирующийся email
            password: new PlainPassword('password123')
        );

        $handler = app(RegisterUserCommandHandler::class);
        $result = $handler->handle(new RegisterUserCommand($dto));

        $this->assertTrue($result->failed());
        $this->assertDatabaseCount('users', 0); // Проверяем, что нового пользователя не добавили
    }
}