<?php

namespace App\Domain\Registration\Services;

use App\Mail\Registration\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;
use Throwable;

class UserService
{
    public function register(array $data): User
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Хотя User::create обычно надёжен, но можно явно проверить, что объект создан:
            if (!$user) {
                throw new Exception('User not created.');
            }

            // Отправка email лучше обернуть в try-catch отдельно
            // Иначе, если почта не отправилась — регистрация не произойдёт вовсе.
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            } catch (Throwable $e) {
                Log::warning('Welcome email failed: ' . $e->getMessage());
                // возможно, показать пользователю мягкое сообщение
            }

            return $user;
        } catch (Exception $e) {
            Log::error(message: 'User registration failed: ' . $e->getMessage());
            throw $e; // можно выбросить своё исключение
        }
    }

    // Метод аутентификации
    public function authenticate(array $data): bool
    {
        try {
            if (Auth::attempt($data)) {
                request()->session()->regenerate();
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error('Authentication failed: ' . $e->getMessage());
            throw new Exception('Authentication failed.');            
        }
    }

    public function logout(): void
    {
        try {
            Auth::logout();
    
            request()->session()->invalidate();
            request()->session()->regenerateToken();
    
            Log::info('User logged out successfully.');
        } catch (Throwable $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            throw new Exception('Logout failed.');
        }
    }

}