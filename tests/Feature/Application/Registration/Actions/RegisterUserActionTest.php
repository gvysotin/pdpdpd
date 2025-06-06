<?php

namespace Tests\Feature\Application\Registration\Actions;

use App\Application\Registration\Actions\RegisterUserAction;
use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Events\Registration\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    public function test_it_registers_user_and_dispatches_event(): void
    {
        Event::fake();
        Log::spy();

        $dto = new UserRegistrationData(
            name: 'Tester',
            email: new Email('test@example.com'),
            password: new PlainPassword('pass1234')
        );

        $createdUser = User::factory()->make();

        $spec = Mockery::mock(EmailSpecificationInterface::class);
        $spec->shouldReceive('check')
            ->once()
            ->with($dto->email);

        $creator = Mockery::mock(UserCreatorInterface::class);
        $creator->shouldReceive('create')
            ->once()
            ->with(Mockery::type(UserRegistrationData::class))
            ->andReturn($createdUser);

        $transaction = Mockery::mock(TransactionManagerInterface::class);
        $transaction->shouldReceive('begin')->once();
        $transaction->shouldReceive('commit')->once();
        $transaction->shouldReceive('rollback')->never();
        $transaction->shouldReceive('afterCommit')->once()->andReturnUsing(fn($callback) => $callback());

        $action = new RegisterUserAction($creator, $spec, Log::getFacadeRoot(), $transaction);

        $result = $action->execute($dto);

        $this->assertTrue($result->succeeded());
        Event::assertDispatched(UserRegistered::class, fn($event) => $event->user === $createdUser);

        Mockery::close();
    }

}