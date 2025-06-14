<?php

namespace Tests\Unit\Application\Registration\Handlers;

use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Registration\Handlers\RegisterUserCommandHandler;
use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Exceptions\DuplicateEmailException;
use App\Domain\Registration\Exceptions\UserPersistenceException;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Events\Registration\UserRegistered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use RuntimeException;
use Exception;
use Mockery;

class RegisterUserCommandHandlerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_registers_user_and_dispatches_event(): void
    {
        Event::fake();

        $userCreator = Mockery::mock(UserCreatorInterface::class);
        $uniqueEmailSpec = Mockery::mock(EmailSpecificationInterface::class);
        $logger = Log::spy();
        $transaction = Mockery::mock(TransactionManagerInterface::class);

        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('secret123')
        );

        $createdUser = User::factory()->make();

        // Проверка email
        $uniqueEmailSpec
            ->shouldReceive('check')
            ->once()
            ->with(Mockery::on(fn($email) => (string) $email === 'test@example.com'));

        // Создание пользователя
        $userCreator
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdUser);

        // Транзакции
        $transaction->shouldReceive('begin')->once();
        $transaction->shouldReceive('commit')->once();
        $transaction->shouldReceive('rollback')->never();
        // Проверяется, что транзакция начинается, завершается, и не откатывается, а afterCommit() запускает callback, 
        // который вызывает событие UserRegistered.
        $transaction->shouldReceive('afterCommit')->once()->andReturnUsing(function (callable $callback) {
            $callback(); // имитируем post-commit callback
        });

        $handler = new RegisterUserCommandHandler(
            userCreator: $userCreator,
            uniqueEmailSpec: $uniqueEmailSpec,
            logger: $logger,
            transaction: $transaction
        );

        $command = new RegisterUserCommand($dto);

        $result = $handler->handle($command);

        // Подтверждаем, что OperationResult — успешен.
        $this->assertTrue($result->succeeded());

        // Laravel проверяет, что событие UserRegistered было отправлено с тем же экземпляром пользователя, который вернул UserCreator.
        Event::assertDispatched(UserRegistered::class, fn($event) => $event->user === $createdUser);

        // Закрывает все моки и проверяет, что все ожидаемые вызовы произошли.
        Mockery::close();
    }

    #[Test]
    public function it_fails_registration_due_to_duplicate_email(): void
    {
        Event::fake(); // Имитация очереди событий

        $userCreator = Mockery::mock(UserCreatorInterface::class);
        $uniqueEmailSpec = Mockery::mock(EmailSpecificationInterface::class);
        $logger = Log::spy();
        $transaction = Mockery::mock(TransactionManagerInterface::class);

        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('secret123')
        );

        // Указываем, что email не уникален
        $uniqueEmailSpec
            ->shouldReceive('check')
            ->once()
            ->andThrow(new DuplicateEmailException);

        $transaction->shouldReceive('begin')->never();
        $transaction->shouldReceive('commit')->never();
        $transaction->shouldReceive('rollback')->once();

        $handler = new RegisterUserCommandHandler(
            userCreator: $userCreator,
            uniqueEmailSpec: $uniqueEmailSpec,
            logger: $logger,
            transaction: $transaction
        );

        $command = new RegisterUserCommand($dto);
        $result = $handler->handle($command);

        $this->assertTrue($result->failed());
        $this->assertSame('Email is already registered', $result->message());

        Event::assertNotDispatched(UserRegistered::class);

        Mockery::close();
    }


    #[Test]
    public function it_rolls_back_if_user_save_fails(): void
    {
        Event::fake(); // Имитация очереди событий

        $userCreator = Mockery::mock(UserCreatorInterface::class);
        $uniqueEmailSpec = Mockery::mock(EmailSpecificationInterface::class);
        $logger = Log::spy();
        $transaction = Mockery::mock(TransactionManagerInterface::class);

        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('secret123')
        );

        $uniqueEmailSpec->shouldReceive('check')->once();

        $transaction->shouldReceive('begin')->once();
        $transaction->shouldReceive('rollback')->once();
        $transaction->shouldReceive('afterCommit')->never();
        $transaction->shouldReceive('commit')->never();

        $userCreator->shouldReceive('create')
            ->once()
            ->andThrow(new UserPersistenceException('DB error'));

        $handler = new RegisterUserCommandHandler(
            userCreator: $userCreator,
            uniqueEmailSpec: $uniqueEmailSpec,
            logger: $logger,
            transaction: $transaction
        );

        $command = new RegisterUserCommand($dto);
        $result = $handler->handle($command);

        $this->assertTrue($result->failed());
        $this->assertSame('DB error', $result->message());

        Event::assertNotDispatched(UserRegistered::class);

        Mockery::close();
    }

    #[Test]
    public function it_rolls_back_on_unexpected_error(): void
    {
        Event::fake(); // Имитация очереди событий

        $userCreator = Mockery::mock(UserCreatorInterface::class);
        $uniqueEmailSpec = Mockery::mock(EmailSpecificationInterface::class);
        $logger = Log::spy();
        $transaction = Mockery::mock(TransactionManagerInterface::class);

        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('secret123')
        );

        $uniqueEmailSpec->shouldReceive('check')->once();

        $transaction->shouldReceive('begin')->once();
        $transaction->shouldReceive('rollback')->once();
        $transaction->shouldReceive('commit')->never();
        $transaction->shouldReceive('afterCommit')->never();

        $userCreator->shouldReceive('create')
            ->once()
            ->andThrow(new RuntimeException('Unexpected failure'));

        $handler = new RegisterUserCommandHandler(
            userCreator: $userCreator,
            uniqueEmailSpec: $uniqueEmailSpec,
            logger: $logger,
            transaction: $transaction
        );

        $command = new RegisterUserCommand($dto);
        $result = $handler->handle($command);

        $this->assertTrue($result->failed());
        $this->assertSame('Failed to register user', $result->message());

        Event::assertNotDispatched(UserRegistered::class);

        Mockery::close();
    }

    #[Test]
    public function it_returns_failure_if_exception_thrown(): void
    {
        $userCreator = Mockery::mock(UserCreatorInterface::class);
        $uniqueEmailSpec = Mockery::mock(EmailSpecificationInterface::class);
        $logger = Log::spy();
        $transaction = Mockery::mock(TransactionManagerInterface::class);

        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('secret123')
        );

        $uniqueEmailSpec
            ->shouldReceive('check')
            ->once()
            ->with(Mockery::on(fn($email) => (string) $email === 'test@example.com'));

        $userCreator
            ->shouldReceive('create')
            ->andThrow(new Exception('Something went wrong'));

        $transaction->shouldReceive('begin')->once();
        $transaction->shouldReceive('rollback')->once();
        $transaction->shouldReceive('commit')->never();
        $transaction->shouldReceive('afterCommit')->never();

        $handler = new RegisterUserCommandHandler(
            userCreator: $userCreator,
            uniqueEmailSpec: $uniqueEmailSpec,
            logger: $logger,
            transaction: $transaction
        );

        $command = new RegisterUserCommand($dto);
        $result = $handler->handle($command);

        $this->assertTrue($result->failed());
        $this->assertEquals('Failed to register user', $result->message());

        $logger->shouldHaveReceived('error')->once();

        Mockery::close();
    }
}