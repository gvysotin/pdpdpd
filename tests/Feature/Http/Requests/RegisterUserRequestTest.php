<?php

namespace Tests\Feature\Http\Requests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterUserRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_required_fields()
    {
        $response = $this->post(route('register.store'), []);
        $response->assertSessionHasErrors(['email', 'name', 'password']);
    }

    #[Test]
    public function it_validates_email_format()
    {
        $response = $this->post(route('register.store'), [
            'email' => 'invalid-email',
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function it_validates_password_confirmation()
    {
        $response = $this->post(route('register.store'), [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password124',
        ]);
        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function it_validates_success_with_correct_data()
    {
        $response = $this->post(route('register.store'), [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect(route('dashboard'));
    }
}
