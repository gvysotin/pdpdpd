<?php

namespace Tests\Feature\Auth;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;    
    /**
     * A basic feature test example.
     */

    /** @test */
    public function registration_page_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200)
            ->assertViewIs('auth.register')
            ->assertSee('Register');
    }

    /** @test */
    public function user_can_register_with_valid_credentials()
    {
        Mail::fake();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ];

        $response = $this->post(route('register'), $userData);

        // Проверка редиректа после успешной регистрации
        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        // Проверка создания пользователя в БД
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Проверка хеширования пароля
        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue(Hash::check($userData['password'], $user->password));

        // Проверка отправки email
        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });
    }

    /** @test */
    public function registration_requires_name()
    {
        $this->post(route('register'), [
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('name');
    }

    /** @test */
    public function registration_requires_valid_email()
    {
        $testCases = [
            '',
            'invalid-email',
            'john@example',
        ];

        foreach ($testCases as $email) {
            $this->post(route('register'), [
                'name' => 'John Doe',
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])->assertSessionHasErrors('email');
        }
    }

    /** @test */
    public function email_must_be_unique()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('email');
    }

    /** @test */
    public function password_must_be_confirmed()
    {
        $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors('password');
    }

    /** @test */
    public function password_must_be_at_least_8_characters()
    {
        $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    }
    
}
