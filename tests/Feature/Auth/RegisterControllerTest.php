<?php

namespace Tests\Feature\Auth;

use App\Domain\Registration\Actions\RegisterUserAction;
use App\Domain\Shared\Results\OperationResult;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_redirects_to_dashboard_when_registration_succeeds(): void
    {
        $this->mock(RegisterUserAction::class)
            ->shouldReceive('execute')
            ->once()
            ->andReturn(OperationResult::success());

        $response = $this->post(route('register'), [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function registration_fails_and_returns_error_on_failure(): void
    {
        $this->mock(RegisterUserAction::class)
            ->shouldReceive('execute')
            ->once()
            ->andReturn(OperationResult::failure('Registration failed.'));

        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'VerySecurePassword123!',
            'password_confirmation' => 'VerySecurePassword123!',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors([
            'general' => 'Registration failed.',
        ]);
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertDatabaseCount('users', 0);
    }

    #[Test]
    public function it_fails_validation_with_invalid_email()
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
    public function it_fails_validation_with_short_password()
    {
        $response = $this->post(route('register'), [
            'name' => 'Valid Name',
            'email' => 'valid@email.com',
            'password' => 'short',       // Пароль меньше 8 символов
            'password_confirmation' => 'short',
        ]);
    
        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function it_fails_validation_with_unmatched_passwords()
    {
        $response = $this->post(route('register'), [
            'name' => 'Valid Name',
            'email' => 'valid@email.com',
            'password' => 'correct-password', // Несоответствие двух полей
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
    public function it_fails_validation_with_existing_email()
    {
        User::factory()->create([
            'email' => 'existing@user.com', // Этот адрес уже занят
        ]);
    
        $response = $this->post(route('register'), [
            'name' => 'Valid Name',
            'email' => 'existing@user.com', // Используем тот же email
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);
    
        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function it_fails_registration_with_invalid_csrf_token(): void
    {
        $response = $this->post('/register', [
            'name' => 'Invalid Csrf',
            'email' => 'invalidcsrf@example.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
            '_token' => 'invalid_token_here',
        ]);
    
        $response->assertStatus(419);
    }
    
    #[Test]
    public function it_succeeds_with_valid_csrf_token(): void
    {
        $token = csrf_token();
        
        $response = $this->post('/register', [
            'name' => 'Valid Csrf',
            'email' => 'validcsrf@example.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
            '_token' => $token,
        ]);
    
        $response->assertRedirect(); // или другой ожидаемый статус
        $this->assertDatabaseHas('users', [
            'email' => 'validcsrf@example.com'
        ]);
    }

    #[Test]
    public function it_fails_validation_with_missing_required_fields(): void
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

}
