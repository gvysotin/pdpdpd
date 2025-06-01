<?php

namespace Tests\Feature\Auth;


use App\Application\Registration\Actions\RegisterUserAction;
use App\Application\Shared\Results\OperationResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function successful_registration_redirects_to_dashboard()
    {
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);
    }

    #[Test]
    public function duplicate_email_returns_error_message()
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->post(route('register'), [
            'name' => 'Jane Doe',
            'email' => 'duplicate@example.com',
            'password' => 'AnotherSecurePassword123!',
            'password_confirmation' => 'AnotherSecurePassword123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function invalid_password_length_shows_error()
    {
        $response = $this->post(route('register'), [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'shrtpas',
            'password_confirmation' => 'shrtpas',
        ]);

        $response->assertSessionHasErrors('password');
    }


    #[Test]
    public function registration_fails_when_exception_occurs()
    {
        $this->instance(RegisterUserAction::class, $this->mock(RegisterUserAction::class));

        $mockAction = $this->mock(RegisterUserAction::class);
        $mockAction->shouldReceive('execute')
            ->once()
            ->andReturn(OperationResult::failure('Registration failed.'));

        $response = $this->post(route('register'), [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'VerySecurePassword123!',
            'password_confirmation' => 'VerySecurePassword123!',
        ]);

        $response->assertSessionHasErrors('general');
    }



    #[Test]
    public function it_handles_registration_failure()
    {
        // Arrange
        $mockAction = $this->mock(RegisterUserAction::class);
        $mockAction->shouldReceive('execute')
            ->once()
            ->andReturn(OperationResult::failure('Registration failed'));

        // Act
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        // Assert
        $response->assertRedirect()
            ->assertSessionHasErrors(['general']);
    }



    
}