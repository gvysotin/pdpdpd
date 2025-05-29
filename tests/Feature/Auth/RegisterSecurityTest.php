<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_stores_the_csrf_token_in_variable(): void
    {
        $this->get(route('register')); // инициализирует сессию и CSRF-токен
        $token = csrf_token();
    
        $this->assertNotEmpty($token); // Проверяем, что токен не пустой
    }


    #[Test]
    public function it_fails_validation_with_xss_in_name_field(): void
    {
        $response = $this->post(route('register'), [
            'name' => '<script>alert("XSS")</script>', // XSS атака
            'email' => 'xss@example.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function it_fails_validation_with_xss_in_email_field(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Valid Name',
            'email' => '<script>alert("XSS")</script>', // XSS атака
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

}