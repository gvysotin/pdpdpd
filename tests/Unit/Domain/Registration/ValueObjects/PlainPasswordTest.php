<?php

namespace Tests\Unit\Domain\Registration\ValueObjects;

use App\Domain\Registration\ValueObjects\HashedPassword;
use App\Domain\Registration\ValueObjects\PlainPassword;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlainPasswordTest extends TestCase
{
    #[Test]
    public function it_throws_exception_for_too_short_password(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PlainPassword('123'); // слишком короткий пароль
    }

    #[Test]
    public function it_hashes_password_correctly(): void
    {
        $plainPassword = new PlainPassword('StrongPassword123');

        $hashed = $plainPassword->hash();

        $this->assertInstanceOf(HashedPassword::class, $hashed);
        $this->assertTrue(Hash::check('StrongPassword123', (string) $hashed));
    }
}
