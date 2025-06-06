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
use RuntimeException;
use Tests\TestCase;

final class UserCreatorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_and_saves_user(): void
    {
        $factory = Mockery::mock(UserFactoryInterface::class);
        $userRepository = Mockery::mock(UserRepositoryInterface::class);

        $creator = new UserCreator($factory, $userRepository);

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

        Mockery::close();        
    }

    #[Test]
    public function it_calls_save_on_created_user(): void
    {
        // 1. Создаем моки для всех зависимостей        
        $factory = Mockery::mock(UserFactoryInterface::class);
        $userRepository = Mockery::mock(UserRepositoryInterface::class);

        // 2. Создаем экземпляр тестируемого класса
        $creator = new UserCreator($factory, $userRepository);

        // 3. Подготавливаем тестовые данные        
        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('secure-password')
        );

        // 4. Создаем мок пользователя
        $userMock = Mockery::mock(User::class);

        // 5. Настраиваем ожидание создания пользователя
        $factory
            ->shouldReceive('createFromDTO')
            ->once()
            ->with(Mockery::on(fn($arg) => $arg->email->value === 'john@example.com'))
            ->andReturn($userMock);

        // 6. Настраиваем ожидание сохранения пользователя
        $userRepository
            ->shouldReceive('save')
            ->once()
            ->with($userMock);

        // 7. Вызываем тестируемый метод
        $result = $creator->create($dto);

        // 8. Проверяем, что вернулся ожидаемый пользователь
        $this->assertSame($userMock, $result);

        // 9. Проверяем, что все ожидания выполнены
        Mockery::close();
    }

    public function it_throws_exception_on_repository_failure(): void
    {
        $this->expectException(UserPersistenceException::class);
        $this->expectExceptionMessage('Failed to save user');

        $dto = new UserRegistrationData(
            name: 'Jane Doe',
            email: new Email('jane@example.com'),
            password: new PlainPassword('failpass')
        );

        $user = new User([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'failpass'
        ]);

        $factory = Mockery::mock(UserFactoryInterface::class);
        $repository = Mockery::mock(UserRepositoryInterface::class);

        $factory->shouldReceive('createFromDTO')
            ->once()
            ->andReturn($user);

        $repository->shouldReceive('save')
            ->once()
            ->with($user)
            ->andThrow(new RuntimeException('DB error'));

        $creator = new UserCreator($factory, $repository);

        $creator->create($dto);

        Mockery::close();
    }


}