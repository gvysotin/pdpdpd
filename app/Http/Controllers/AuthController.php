<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    //
    public function register()
    {
        return view("auth.register");
    }

    public function store(CreateUserRequest $request)
    {
        // Получаем проверенные данные непосредственно из объекта запроса
        $validatedData = $request->validated();

        try {

            // Cоздаём пользователя и сохраняем его в переменную $user
            $user = User::create(
                [
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($validatedData['password'])
                ]
            );

            // Хотя User::create обычно надёжен, но можно явно проверить, что объект создан:
            if (!$user) {
                throw new \Exception('User not created.');
            }

            // Отправка email лучше обернуть в try-catch отдельно
            // Иначе, если почта не отправилась — регистрация не произойдёт вовсе.
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            } catch (\Throwable $e) {
                Log::warning('Welcome email failed: ' . $e->getMessage());
                // возможно, показать пользователю мягкое сообщение
            }

            return redirect()->route('dashboard')->with('success', 'Account created successfully!');

        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return back()->with('error', 'Registration failed. Please try again.');
        }
    }

    public function login()
    {
        return view("auth.login");
    }

    public function authenticate()
    {

        //
        //dd(request()->all());

        $validated = request()->validate(
            [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]
        );

        if (Auth::attempt($validated)) {
            request()->session()->regenerate();

            // // Попытка отправить письмо при аутентификации. Проверка работы почты.
            // $user = User::where('email', $validated['email'])->first();
            // if ($user && Hash::check($validated['password'], $user->password)) {
            //     Mail::to($user->email)->send(new WelcomeEmail($user));
            // }

            return redirect()->route('dashboard')->with('success', 'Logged is successfully');
        }

        return redirect()->route('login')->withErrors([
            'email' => 'No matching user found with the provided email and password.',
        ]);
    }


    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('dashboard')->with('success', 'Logged out successfully');

    }

}
