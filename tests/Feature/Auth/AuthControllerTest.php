<?php

namespace Tests\Feature\Auth;

use App\Domain\Registration\Actions\RegisterUserAction;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Shared\Results\OperationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_processes_registration_correctly()
    {

        // Arrange
        $mockAction = $this->mock(RegisterUserAction::class);
        $mockAction->shouldReceive('execute')
            ->once()
            ->withArgs(function (UserRegistrationData $dto) {
                return $dto->email->value === 'test@example.com';
            });

        // Act
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!'
        ]);

        // Assert
        $response->assertRedirectToRoute('dashboard')
            ->assertSessionHas('success');
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