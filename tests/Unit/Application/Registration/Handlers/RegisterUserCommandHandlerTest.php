<?php

namespace Tests\Unit\Application\Registration\Handlers;

use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Registration\Handlers\RegisterUserCommandHandler;
use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Events\Registration\UserRegistered;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

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

        // Email проверка
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
        $transaction->shouldReceive('afterCommit')->once()->andReturnUsing(function (callable $callback) {
            $callback(); // имитируем выполнение callback'а
        });

        $handler = new RegisterUserCommandHandler(
            userCreator: $userCreator,
            uniqueEmailSpec: $uniqueEmailSpec,
            logger: $logger,
            transaction: $transaction
        );

        $command = new RegisterUserCommand($dto);

        $result = $handler->handle($command);

        $this->assertTrue($result->succeeded());

        Event::assertDispatched(UserRegistered::class, fn($event) => $event->user === $createdUser);

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