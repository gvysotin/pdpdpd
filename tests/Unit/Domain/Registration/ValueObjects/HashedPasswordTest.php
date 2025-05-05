<?php

namespace Tests\Unit\Domain\Registration\ValueObjects;

use App\Domain\Registration\ValueObjects\HashedPassword;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HashedPasswordTest extends TestCase
{
    #[Test]
    public function it_creates_hashed_password(): void
    {
        $hashedPassword = new HashedPassword('$2y$10$abcdefghijklmnopqrstuv');

        $this->assertSame('$2y$10$abcdefghijklmnopqrstuv', (string) $hashedPassword);
    }
   
}
