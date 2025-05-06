<?php

namespace Tests\Feature\Domain\Registration;

use App\Application\Registration\Actions\RegisterUserAction;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\{Email, PlainPassword};
use App\Domain\Registration\Exceptions\UserRegistrationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DuplicateEmailRegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_prevents_duplicate_registration_with_same_email(): void
    {
        $firstUserData = new UserRegistrationData(
            name: 'First User',
            email: new Email('example@test.com'),
            password: new PlainPassword('password123')
        );
        
        $action = app(RegisterUserAction::class);
        
        // Первая регистрация - успех
        $firstResult = $action->execute($firstUserData);
        $this->assertTrue($firstResult->succeeded());
        
        // Вторая попытка - должна быть ошибка
        $secondUserData = clone $firstUserData;
        $secondResult = $action->execute($secondUserData);
        
        $this->assertTrue($secondResult->failed());
        $this->assertEquals('Email already registered', $secondResult->message());
    }
}

