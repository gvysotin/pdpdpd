<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Tests\TestCase;

class RegistrationRouteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_view_registration_form()
    {
        $response = $this->get(route('register'));
        $response->assertOk();
    }

    #[Test]
    public function authenticated_user_is_redirected_away_from_registration_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('register'));

        $response->assertRedirect('/');
    }

    #[Test]
    public function registration_is_rate_limited_after_too_many_attempts(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '123.123.123.123']);

        // Убеждаемся, что кеш очищен перед тестом
        Cache::flush();
        RateLimiter::clear('registration');

        for ($i = 0; $i < 10; $i++) {
            // Первые 10 запросов должны пройти
            $this->post(route('register.store'), [
                'name' => 'User',
                'email' => "user$i@example.com",
                'password' => 'password',
                'password_confirmation' => 'password'
            ])->assertRedirect();
        }

        // А 11-й запрос должен быть отклонен
        $this->post(route('register.store'), [
            'name' => 'User',
            'email' => 'blocked@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ])->assertStatus(429);
    }

    #[Test]
    public function registration_rate_limit_returns_retry_after_header(): void
    {
        RateLimiter::clear('registration');
    
        for ($i = 0; $i < 11; $i++) {
            $this->post(route('register.store'), [
                'name' => "User $i",
                'email' => "user$i@example.com",
                'password' => 'password',
                'password_confirmation' => 'password'
            ]);
        }
    
        $response = $this->post(route('register.store'), [
            'name' => 'Blocked',
            'email' => 'blocked@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);
    
        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
    }

}