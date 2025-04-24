<?php

namespace Tests\Feature\Auth;

use App\Actions\RegisterUserAction;
use App\DataTransferObjects\UserRegistrationData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

use App\Support\Results\OperationResult;
use App\Support\Results\ResultStatus;

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