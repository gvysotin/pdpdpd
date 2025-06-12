<?php

namespace Tests\Feature\Auth;

use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterSuccessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_redirects_to_dashboard_on_successful_registration(): void
    {
        // Мокаем все зависимости handler'а
        $userCreatorMock = $this->mock(UserCreatorInterface::class);
        $emailSpecMock = $this->mock(EmailSpecificationInterface::class);
        $loggerMock = $this->mock(LoggerInterface::class);
        $transactionMock = $this->mock(TransactionManagerInterface::class);

        // Настраиваем ожидания для успешного сценария
        $emailSpecMock->shouldReceive('check')->once();
        $transactionMock->shouldReceive('begin')->once();
        $userCreatorMock->shouldReceive('create')->once()->andReturn(new User);
        $transactionMock->shouldReceive('afterCommit')->once();
        $transactionMock->shouldReceive('commit')->once();
        $loggerMock->shouldReceive('info')->twice();

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