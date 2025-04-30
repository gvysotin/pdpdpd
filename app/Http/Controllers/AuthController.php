<?php

namespace App\Http\Controllers;

use App\Domain\Registration\Actions\RegisterUserAction;
use App\Domain\Registration\Services\UserService;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class AuthController extends Controller
{

    public function __construct(protected UserService $userService) {}

    public function register()
    {
        return view("auth.register");
    }

    public function store(CreateUserRequest $request, RegisterUserAction $action)
    {
        $result = $action->execute($request->toDTO());

        if ($result->failed()) {
            return back()
                ->withInput()
                ->withErrors([
                    'general' => $result->message ?? 'Something went wrong.',
                ]);
        }

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    public function login()
    {
        return view("auth.login");
    }

    public function authenticate(LoginRequest $request)
    {

        //
        //dd(request()->all());

        $validated = request()->validate(
            [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]
        );

        if(Auth::attempt($validated)) {
            request()->session()->regenerate();

            // // Попытка отправить письмо при аутентификации. Проверка работы почты.
            // $user = User::where('email', $validated['email'])->first();
            // if ($user && Hash::check($validated['password'], $user->password)) {
            //     Mail::to($user->email)->send(new WelcomeEmail($user));
            // }

            return redirect()->route('dashboard')->with('success','Logged is successfully');
        }

        return redirect()->route('login')->withErrors([
            'email'=> 'No matching user found with the provided email and password.',
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
