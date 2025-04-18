<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Mail\WelcomeEmail;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class AuthController extends Controller
{

    public function __construct(protected UserService $userService) {}

    //
    public function register()
    {
        return view("auth.register");
    }

    public function store(CreateUserRequest $request)
    {

        try {
            $validatedData = $request->validated();

            $this->userService->register($validatedData);

            return redirect()->route('dashboard')->with('success', 'Account created successfully!');
        } catch (Exception $e) {
            Log::error('Registration failed in controller: ' . $e->getMessage());

            return back()->with('error', 'Registration failed. Please try again.');
        }        


    }

    public function login()
    {
        return view("auth.login");
    }

    public function authenticate(LoginRequest $request)
    {
        $validatedData = $request->validated();

        // Вызов метода authenticate из сервиса
        if ($this->userService->authenticate($validatedData)) {
            return redirect()->route('dashboard')->with('success', 'Logged in successfully');
        }

        // В случае неудачи — редирект с ошибкой
        return redirect()->route('login')->withErrors([
            'email' => 'No matching user found with the provided email and password.',
        ]);


        // //
        // //dd(request()->all());

        // $validated = request()->validate(
        //     [
        //         'email' => 'required|email',
        //         'password' => 'required|min:8',
        //     ]
        // );

        // if (Auth::attempt($validated)) {
        //     request()->session()->regenerate();

        //     return redirect()->route('dashboard')->with('success', 'Logged is successfully');
        // }

        // return redirect()->route('login')->withErrors([
        //     'email' => 'No matching user found with the provided email and password.',
        // ]);
    }


    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('dashboard')->with('success', 'Logged out successfully');

    }

}
