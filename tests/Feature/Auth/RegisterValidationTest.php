<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_valid_email(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test',
            'email' => 'invalid-email',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function it_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@user.com']);

        $response = $this->post(route('register'), [
            'name' => 'Valid Name',
            'email' => 'existing@user.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function it_requires_strong_password(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Valid Name',
            'email' => 'valid@email.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function it_requires_password_confirmation_to_match(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Valid Name',
            'email' => 'valid@email.com',
            'password' => 'correct-password',
            'password_confirmation' => 'wrong-confirmation',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function it_fails_validation_with_short_name()
    {
        $response = $this->post(route('register'), [
            'name' => 'ab',              // Имя короче минимально допустимой длины
            'email' => 'valid@email.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);
    
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function it_fails_validation_with_long_name()
    {
        $response = $this->post(route('register'), [
            'name' => 'NameIsTooLongAndExceedsMaximumAllowedLengthOfFortyCharactersForSomeReason', // Длина превышает максимум
            'email' => 'valid@email.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);
    
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function it_requires_required_fields(): void
    {
        $response = $this->post(route('register'), []);
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function it_fails_validation_with_script_in_name(): void
    {
        $response = $this->post(route('register'), [
            'name' => '<script>alert("XSS")</script>',
            'email' => 'xss@example.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    // Тест для максимальной длины пароля
    #[Test]
    public function it_requires_password_maximum_length(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Valid Name',
            'email' => 'valid@email.com',
            'password' => str_repeat('a', 257), // пароли больше 256 символов
            'password_confirmation' => str_repeat('a', 257),
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function it_shows_custom_error_message_for_invalid_name(): void
    {
        $response = $this->post(route('register'), [
            'name' => '<script>alert("XSS")</script>',
            'email' => 'valid@email.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);
    
        $response->assertSessionHasErrors(['name']);
        
        // Получаем первую ошибку для поля name
        $errorMessage = session('errors')->first('name');
        //dump($errorMessage);  // или dd($errorMessage);
   
        // Проверяем, что есть сообщение об ошибке (точный текст не важен)
        $this->assertNotNull($errorMessage);
        
        // Альтернативно: проверяем что ошибка относится к валидации строки
        $this->assertStringContainsString('Field name content HTML tags, which is not allowed', $errorMessage);
    }

    #[Test]
    public function it_shows_custom_error_message_for_invalid_password(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Valid Name',
            'email' => 'valid@email.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
    
        $response->assertSessionHasErrors(['password']);
        
        // Получаем первую ошибку для поля password
        $errorMessage = session('errors')->first('password');
        //dump($errorMessage);  // или dd($errorMessage);
          
        // Проверяем, что сообщение содержит информацию о минимальной длине
        $this->assertStringContainsString('The password field must be at least 8 characters.', $errorMessage);
    }


}