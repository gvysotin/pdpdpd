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
}