<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_request_with_valid_csrf_token(): void
    {
        $this->get(route('register'));

        $token = csrf_token();

        $response = $this->post(route('register'), [
            '_token' => $token,
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
            'password' => 'securepassword',
            'password_confirmation' => 'securepassword',
        ]);

        $response->assertStatus(302); // редирект при успешной регистрации
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