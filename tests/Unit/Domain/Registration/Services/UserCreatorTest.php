<?php

namespace Tests\Unit\Domain\Registration\Services;

use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\Contracts\UserRepositoryInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Exceptions\UserRegistrationException;
use App\Domain\Registration\Services\UserCreator;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserCreatorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_and_saves_user(): void
    {
        $factory = Mockery::mock(UserFactoryInterface::class);
        $spec = Mockery::mock(EmailSpecificationInterface::class);
        $userRepository = Mockery::mock(UserRepositoryInterface::class);

        $creator = new UserCreator($factory, $spec, $userRepository);

        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('hashed-password')
        );

        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'hashed-password'
        ]);

        $spec
            ->shouldReceive('check')
            ->once()
            ->with(Mockery::on(fn($email) => (string) $email === 'john@example.com'));

        $factory
            ->shouldReceive('createFromDTO')
            ->once()
            ->with(Mockery::on(fn($arg) => $arg->email->value === 'john@example.com'))
            ->andReturn($user);

        // Добавляем ожидание вызова save()
        $userRepository
            ->shouldReceive('save')
            ->once()
            ->with($user);

        $result = $creator->create($dto);
        $this->assertEquals($user, $result);
    }

    #[Test]
    public function it_throws_exception_when_email_is_not_unique(): void
    {
        $factory = Mockery::mock(UserFactoryInterface::class);
        $spec = Mockery::mock(EmailSpecificationInterface::class);
        $userRepository = Mockery::mock(UserRepositoryInterface::class);

        $creator = new UserCreator($factory, $spec, $userRepository);

        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('password')
        );

        $spec
            ->shouldReceive('check')
            ->once()
            ->with(Mockery::on(fn($email) => (string) $email === 'john@example.com'))
            ->andThrow(new UserRegistrationException('Email already registered'));

        $this->expectException(UserRegistrationException::class);
        $this->expectExceptionMessage('Email already registered');

        $creator->create($dto);
    }

    #[Test]
    public function it_calls_save_on_created_user(): void
    {
        // 1. Создаем моки для всех зависимостей        
        $factory = Mockery::mock(UserFactoryInterface::class);
        $spec = Mockery::mock(EmailSpecificationInterface::class);
        $userRepository = Mockery::mock(UserRepositoryInterface::class);


        // 2. Создаем экземпляр тестируемого класса
        $creator = new UserCreator($factory, $spec, $userRepository);

        // 3. Подготавливаем тестовые данные        
        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('secure-password')
        );

        // 4. Настраиваем ожидания для проверки email
        $spec
            ->shouldReceive('check')
            ->once()
            ->with(Mockery::on(fn($email) => (string) $email === 'john@example.com'));


        // 5. Создаем мок пользователя
        $userMock = Mockery::mock(User::class);

        // 6. Настраиваем ожидание создания пользователя
        $factory
            ->shouldReceive('createFromDTO')
            ->once()
            ->with(Mockery::on(fn($arg) => $arg->email->value === 'john@example.com'))
            ->andReturn($userMock);

        // 7. Настраиваем ожидание сохранения пользователя
        $userRepository
            ->shouldReceive('save')
            ->once()
            ->with($userMock);

        // 8. Вызываем тестируемый метод
        $result = $creator->create($dto);

        // 9. Проверяем, что вернулся ожидаемый пользователь
        $this->assertSame($userMock, $result);

        // 10. Проверяем, что все ожидания выполнены
        Mockery::close();
    }

}