<?php

namespace Tests\Unit\Domain\Registration\Actions;

use App\Application\Registration\Actions\RegisterUserAction;
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

class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_registers_user_and_dispatches_event(): void
    {
        Event::fake();

        $userCreator = Mockery::mock(UserCreatorInterface::class);
        $logger = Log::spy();

        $transaction = Mockery::mock(TransactionManagerInterface::class);

        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('secret123')
        );

        $createdUser = User::factory()->make();

        // Ожидания для UserCreator
        $userCreator
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdUser);

        // Ожидаем начало, коммит и afterCommit
        $transaction->shouldReceive('begin')->once();
        $transaction->shouldReceive('commit')->once();
        $transaction->shouldReceive('rollback')->never(); // Ожидаем, что не будет вызван
        $transaction->shouldReceive('afterCommit')->once()->andReturnUsing(function (callable $callback) {
            $callback(); // Выполним коллбэк сразу — как будто коммит прошёл
        });

        // Создаём экземпляр тестируемого класса со всеми зависимостями
        // передаем три параметра в конструктор
        $action = new RegisterUserAction($userCreator, $logger, $transaction);

        // Выполняем тестируемый метод        
        $result = $action->execute($dto);

        // Проверяем результаты
        $this->assertTrue($result->succeeded());
        Event::assertDispatched(UserRegistered::class, fn($event) => $event->user === $createdUser);
    }

    #[Test]
    public function it_returns_failure_if_exception_thrown(): void
    {
        $userCreator = Mockery::mock(UserCreatorInterface::class);
        $logger = Log::spy();

        $transaction = Mockery::mock(TransactionManagerInterface::class);

        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('secret123')
        );

        $userCreator
            ->shouldReceive('create')
            ->andThrow(new Exception('Something went wrong'));

        // Ожидаем начало, коммит и afterCommit
        $transaction->shouldReceive('begin')->once();
        $transaction->shouldReceive('rollback')->once();

        $action = new RegisterUserAction($userCreator, $logger, $transaction);

        $result = $action->execute($dto);

        $this->assertTrue($result->failed());
        $this->assertEquals('Failed to register user', $result->message());

        $logger->shouldHaveReceived('error')->once();
    }
}
