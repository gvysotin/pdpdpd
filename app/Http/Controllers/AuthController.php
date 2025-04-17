<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    //
    public function register()
    {
        return view("auth.register");
    }

    public function store()
    {


        $validated = request()->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:40',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'regex:/^[\x20-\x7E]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/u',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
            ],
        ], [       
            'email.required' => 'Please, fill email.',
            'email.string' => 'Email must be a string.',
            'email.email' => 'Email must be a correct email adress.',
            'email.regex' => 'Email must be content only latin symbols and conform to format.',
            'email.unique' => 'This email is already registered.',       
        ]);

        // Здесь мы выполняем нашу проверку
        // $validated = request()->validate(
        //     [
        //         'name' => 'required|min:3|max:40',
        //         'email' => [
        //             'required',
        //             'email' => [
        //                 'required',
        //                 'email',
        //                 'unique:users,email',
        //             ],
        //             'unique:users,email'
        //         ],
        //         'password' => 'required|confirmed|min:8',
        //     ]
        // );

        // Здесь мы создаём пользователя
        // Я собираюсь сохранить его в переменную $user
        $user = User::create(
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'])
            ]
        );

        Mail::to($user->email)->send(new WelcomeEmail($user));

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
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
