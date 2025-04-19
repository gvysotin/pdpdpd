<?php

namespace App\Http\Controllers;

use App\Actions\RegisterUserAction;
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

    public function store(CreateUserRequest $request, RegisterUserAction $action)
    {
        $action->execute($request->validated());
    
        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
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

    }


    public function logout()
    {

        try {
            $this->userService->logout();
    
            return redirect()->route('dashboard')->with('success', 'Logged out successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Logout failed. Please try again.');
        }

    }

}
