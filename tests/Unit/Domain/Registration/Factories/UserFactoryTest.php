<?php

namespace Tests\Unit\Domain\Registration\Factories;

use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Factories\UserFactory;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\HashedPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_user_correctly(): void
    {
        $factory = new UserFactory();

        $dto = new UserRegistrationData(
            name: 'Jane Doe',
            email: new Email('jane@example.com'),
            password: new HashedPassword(bcrypt('securePassword'))
        );

        $user = $factory->createFromDTO($dto );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Jane Doe', $user->name);
        $this->assertEquals('jane@example.com', $user->email);
        $this->assertTrue(Hash::check('securePassword', $user->password));
    }
}
