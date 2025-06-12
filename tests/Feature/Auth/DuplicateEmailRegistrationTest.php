<?php

namespace Tests\Feature\Auth;

use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Registration\Handlers\RegisterUserCommandHandler;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\{Email, PlainPassword};
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DuplicateEmailRegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_prevents_duplicate_registration_with_same_email(): void
    {
        // Явно создаем транзакционного менеджера
        $transactionManager = app(TransactionManagerInterface::class);

        // Подготовка тестовых данных
        $firstUserData = new UserRegistrationData(
            name: 'First User',
            email: new Email('example@test.com'),
            password: new PlainPassword('password123')
        );

        // Получаем обработчик из контейнера
        $handler = app(RegisterUserCommandHandler::class);

        // Первая регистрация - успех
        $firstResult = $transactionManager->run(function () use ($handler, $firstUserData) {
            return $handler->handle(new RegisterUserCommand($firstUserData));
        });

        $this->assertTrue($firstResult->succeeded());

        // Проверяем, что пользователь действительно создан
        $this->assertDatabaseHas('users', [
            'email' => 'example@test.com'
        ]);

        $secondResult = $transactionManager->run(function () use ($handler, $firstUserData) {
            return $handler->handle(new RegisterUserCommand($firstUserData));
        });

        // Вторая попытка - должна быть ошибка
        $this->assertTrue($secondResult->failed());
        $this->assertEquals('Email already registered', $secondResult->message());

    }

    #[Test]
    public function it_allows_registration_with_different_emails(): void
    {
        // Явно создаем транзакционного менеджера
        $transactionManager = app(TransactionManagerInterface::class);

        $firstUserData = new UserRegistrationData(
            name: 'First User',
            email: new Email('first@test.com'),
            password: new PlainPassword('password123')
        );

        $secondUserData = new UserRegistrationData(
            name: 'Second User',
            email: new Email('second@test.com'), // Другой email
            password: new PlainPassword('password456')
        );

        // Получаем обработчик из контейнера        
        $handler = app(RegisterUserCommandHandler::class);

        // Первая регистрация

        // Первая регистрация - успех
        $firstResult = $transactionManager->run(function () use ($handler, $firstUserData) {
            return $handler->handle(new RegisterUserCommand($firstUserData));
        });

        $this->assertTrue($firstResult->succeeded());

        // Вторая регистрация с другим email - должна быть успешной
        $secondResult = $transactionManager->run(function () use ($handler, $secondUserData) {
            return $handler->handle(new RegisterUserCommand($secondUserData));
        });

        $this->assertTrue($secondResult->succeeded());

        // Проверяем, что в БД два пользователя
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'email' => 'first@test.com'
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'second@test.com'
        ]);
    }
}