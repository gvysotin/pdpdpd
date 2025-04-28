<?php

namespace Tests\Unit\Domain\Registration\DTO;

use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserRegistrationDataTest extends TestCase
{
    #[Test]
    public function it_hashes_plain_password_correctly(): void
    {
        $dto = new UserRegistrationData(
            name: 'John Doe',
            email: new Email('john@example.com'),
            password: new PlainPassword('password123')
        );

        $hashedDto = $dto->withHashedPassword();

        $this->assertTrue(Hash::check('password123', (string) $hashedDto->password));
    }
}
