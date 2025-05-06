<?php

namespace Tests\Feature\Domain\Registration;

use App\Application\Registration\Actions\RegisterUserAction;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\{Email, PlainPassword};
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Throwable;

class ConcurrentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]    
    public function test_concurrent_registration_with_same_email()
    {
        $email = 'duplicate@example.com';
        $data = new UserRegistrationData(
            name: 'Test',
            email: new Email($email),
            password: new PlainPassword('password')
        );
    
        try {
            DB::transaction(function () use ($data) {
                $action1 = app(RegisterUserAction::class);
                $result1 = $action1->execute($data);
                $this->assertTrue($result1->succeeded());
    
                $action2 = app(RegisterUserAction::class);
                $result2 = $action2->execute($data);
                $this->assertTrue($result2->failed());
    
                // Вызываем исключение, чтобы откатить транзакцию
                throw new Exception('Rollback');
            });
        } catch (Exception $e) {
            $this->assertEquals('Rollback', $e->getMessage());
        }
    
        // Убеждаемся, что ничего не записалось
        $this->assertDatabaseMissing('users', ['email' => $email]);
    }
   
    #[Test]
    public function it_prevents_duplicate_registration_with_same_email(): void
    {
        $email = new Email('duplicate@example.com');
        $password = new PlainPassword('securePassword');

        $data = new UserRegistrationData(
            name: 'Test User',
            email: $email,
            password: $password
        );

        try {
            DB::beginTransaction();

            // Первая регистрация проходит успешно
            $action1 = app(RegisterUserAction::class);
            $result1 = $action1->execute($data);
            $this->assertTrue($result1->succeeded());

            // Симулируем конкурентную попытку (до коммита первой транзакции)
            // — Laravel не знает, что email уже "занят", но база данных должна поймать это
            $action2 = app(RegisterUserAction::class);
            $result2 = $action2->execute($data);
            $this->assertTrue($result2->failed());

            // Если вторая прошла — тест неудачен
            $this->fail('Second registration should have failed due to duplicate email.');
        } catch (Throwable $e) {
            //dump($e->getMessage());
            $this->assertStringContainsString('duplicate email', $e->getMessage());
        } finally {
            DB::rollBack();
        }

        // Убедимся, что пользователь всё равно не сохранён
        $this->assertDatabaseMissing('users', ['email' => $email->value]);
    }

}