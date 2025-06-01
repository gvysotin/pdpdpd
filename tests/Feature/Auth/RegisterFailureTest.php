<?php

namespace Tests\Feature\Auth;

use App\Application\Registration\Actions\RegisterUserAction;

use App\Application\Shared\Results\OperationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterFailureTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_handles_failed_registration_by_redisplaying_form_with_errors(): void
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
        $response->assertSessionHasErrors(['general' => 'Registration failed.']);
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertDatabaseCount('users', 0);
    }
}