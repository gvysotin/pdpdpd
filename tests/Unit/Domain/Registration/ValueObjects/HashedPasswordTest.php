<?php

namespace Tests\Unit\Domain\Registration\ValueObjects;

use App\Domain\Registration\ValueObjects\HashedPassword;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HashedPasswordTest extends TestCase
{
    #[Test]
    public function it_creates_hashed_password(): void
    {
        // Задаем переменную с корректным хешем пароля
        $hashedPasswordValue = password_hash('your_password', PASSWORD_BCRYPT);

        // Создаем объект HashedPassword, передавая ему хеш пароля
        $hashedPassword = new HashedPassword($hashedPasswordValue);

        // Проверяем, что строковое представление объекта HashedPassword соответствует исходному хешу
        $this->assertSame($hashedPasswordValue, (string) $hashedPassword);

    }
   
}
