<?php

namespace Tests\Feature\Auth;

use App\Domain\Registration\Actions\RegisterUserAction;
use App\Domain\Shared\Results\OperationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function registration_redirects_to_dashboard_on_success(): void
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
    }
}
