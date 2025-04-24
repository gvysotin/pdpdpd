<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class RegistrationRouteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_access_registration()
    {
        $response = $this->get(route('register'));
        $response->assertOk();
    }

    #[Test]
    public function authenticated_user_redirected_from_registration()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('register'));

        $response->assertRedirect('/');
    }

    #[Test]
    public function registration_has_rate_limiting(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '123.123.123.123']);

        // Убедитесь, что кеш очищен перед тестом
        Cache::flush();
        RateLimiter::clear('registration');

        // Первый запрос должен пройти
        $this->post(route('register.store'), [
            'name' => 'User',
            'email' => 'first@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ])->assertRedirect();

        // Второй запрос должен быть отклонен
        $this->post(route('register.store'), [
            'name' => 'User',
            'email' => 'blocked@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ])->assertStatus(429);
    }

    #[Test]
    public function registration_has_rate_limiting2(): void
    {
        // Проверяем непосредственно RateLimiter
        $this->assertTrue(RateLimiter::remaining('registration', '127.0.0.1') > 0);
        
        // Первый запрос
        $this->post(route('register.store'), [
            'name' => 'User',
            'email' => 'first@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ])->assertRedirect();
        
        $this->assertEquals(0, RateLimiter::remaining('registration', '127.0.0.1'));
        
        // Второй запрос
        $response = $this->post(route('register.store'), [
            'name' => 'User',
            'email' => 'blocked@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);
        $response->assertStatus(429);
    }

}