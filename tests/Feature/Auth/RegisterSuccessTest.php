<?php

namespace Tests\Feature\Auth;

use App\Application\Registration\Actions\RegisterUserAction;
use App\Domain\Shared\Results\OperationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterSuccessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_redirects_to_dashboard_on_successful_registration(): void
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
}