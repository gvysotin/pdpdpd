<?php

namespace Tests\Unit\Domain\Registration\ValueObjects;

use App\Domain\Registration\ValueObjects\Email;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use InvalidArgumentException;

class EmailTest extends TestCase
{
    #[Test]
    public function it_creates_email_with_valid_value(): void
    {
        $email = new Email('Test@Example.com');

        $this->assertEquals('test@example.com', (string) $email);
    }

    #[Test]
    public function it_throws_exception_for_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('invalid-email');
    }

    #[Test]
    public function it_normalizes_email_to_lowercase(): void
    {
        $email = new Email('JOHN@EXAMPLE.COM');

        $this->assertSame('john@example.com', (string) $email);
    }

    #[Test]
    public function it_compares_two_emails_correctly(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('TEST@example.com');

        $this->assertTrue($email1->equals($email2));
    }
}
