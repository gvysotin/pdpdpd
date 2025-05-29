<?php

namespace Tests\Unit\Domain\Registration\Services;

use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserFactoryInterface;
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

        $creator = new UserCreator($factory, $spec);

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

        // Изменено с isSatisfiedBy на check
        $spec
            ->shouldReceive('check')
            ->once()
            ->with(Mockery::on(fn($email) => (string)$email === 'john@example.com'));

        $factory
            ->shouldReceive('createFromDTO')
            ->once()
            ->with(Mockery::on(fn($arg) => $arg->email->value === 'john@example.com'))
            ->andReturn($user);

        $creator->create($dto);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    #[Test]
    public function it_throws_exception_when_email_is_not_unique(): void
    {
        $factory = Mockery::mock(UserFactoryInterface::class);
        $spec = Mockery::mock(EmailSpecificationInterface::class);

        $creator = new UserCreator($factory, $spec);

        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('password')
        );

        // Изменено с isSatisfiedBy на check
        $spec
            ->shouldReceive('check')
            ->once()
            ->with(Mockery::on(fn($email) => (string)$email === 'john@example.com'))
            ->andThrow(new UserRegistrationException('Email already registered'));

        $this->expectException(UserRegistrationException::class);
        $this->expectExceptionMessage('Email already registered');

        $creator->create($dto);
    }

    #[Test]
    public function it_calls_save_on_created_user(): void
    {
        $factory = Mockery::mock(UserFactoryInterface::class);
        $spec = Mockery::mock(EmailSpecificationInterface::class);
    
        $creator = new UserCreator($factory, $spec);
    
        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('secure-password')
        );
    
        // Изменено с isSatisfiedBy на check
        $spec
            ->shouldReceive('check')
            ->once()
            ->with(Mockery::on(fn($email) => (string)$email === 'john@example.com'));
    
        $userMock = Mockery::mock(User::class);
        $userMock
            ->shouldReceive('save')
            ->once();
    
        $factory
            ->shouldReceive('createFromDTO')
            ->once()
            ->with(Mockery::on(fn($arg) => $arg->email->value === 'john@example.com'))
            ->andReturn($userMock);
    
        $creator->create($dto);
    }
}