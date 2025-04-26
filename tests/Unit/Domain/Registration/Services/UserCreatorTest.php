<?php

namespace Tests\Unit\Domain\Registration\Services;

use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Services\UserCreator;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\HashedPassword;
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
        $creator = new UserCreator($factory);

        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new HashedPassword('hashed-password')
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

        $creator->create($dto);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

}
