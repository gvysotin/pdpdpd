<?php

namespace Tests\Unit\Domain\Registration\Actions;


use App\Application\Registration\Actions\RegisterUserAction;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use App\Domain\Shared\Results\OperationResult;
use App\Events\Registration\UserRegistered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
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

        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('secret123')
        );

        $createdUser = User::factory()->make();

        $userCreator
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdUser);

        $action = new RegisterUserAction($userCreator, $logger);

        $result = $action->execute($dto);

        $this->assertTrue($result->succeeded());

        Event::assertDispatched(UserRegistered::class, fn ($event) => $event->user === $createdUser);
    }

    #[Test]
    public function it_returns_failure_if_exception_thrown(): void
    {
        $userCreator = Mockery::mock(UserCreatorInterface::class);
        $logger = Log::spy();

        $dto = new UserRegistrationData(
            name: 'Test User',
            email: new Email('test@example.com'),
            password: new PlainPassword('secret123')
        );

        $userCreator
            ->shouldReceive('create')
            ->andThrow(new \Exception('Something went wrong'));

        $action = new RegisterUserAction($userCreator, $logger);

        $result = $action->execute($dto);

        $this->assertTrue($result->failed());
        $this->assertEquals('Failed to register user', $result->message());

        $logger->shouldHaveReceived('error')->once();
    }
}
