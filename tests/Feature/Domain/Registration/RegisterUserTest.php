<?php

namespace Tests\Feature\Domain\Registration;

use App\Domain\Registration\Actions\RegisterUserAction;
use App\Domain\Shared\Results\OperationResult;
use App\Events\Registration\UserRegistered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_registers_user_successfully(): void
    {
        Event::fake([UserRegistered::class]);

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    #[Test]
    public function it_dispatches_user_registered_event(): void
    {
        Event::fake([UserRegistered::class]);

        $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        Event::assertDispatched(UserRegistered::class, fn ($e) => $e->user->is($user));
    }

    #[Test]
    public function it_handles_registration_failure_gracefully(): void
    {
        // Подготовка фейковых данных запроса
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    
        // Мокаем действие регистрации
        $mockedAction = Mockery::mock(RegisterUserAction::class);
        $mockedAction
            ->shouldReceive('execute')
            ->once()
            ->andReturn(OperationResult::failure('Something went wrong'));
    
        // Подменяем реализацию в контейнере
        $this->app->instance(RegisterUserAction::class, $mockedAction);
    
        // Выполняем запрос
        $response = $this->post(route('register'), $payload);
    
        // Проверка, что редирект на ту же страницу (или куда настроено)
        $response->assertRedirect();
    
        // Проверка, что есть флэш-сообщение об ошибке
        $response->assertSessionHas('error', 'Something went wrong');
    
        // Убедимся, что пользователь не создан
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }



}
