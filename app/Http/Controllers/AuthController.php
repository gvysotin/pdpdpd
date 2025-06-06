<?php

namespace App\Http\Controllers;

use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Registration\Contracts\RegisterUserHandlerInterface;
use App\Domain\Registration\Services\UserService;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(protected UserService $userService)
    {
    }

    public function register(): View
    {
        return view("auth.register");
    }

    public function store(CreateUserRequest $request, RegisterUserHandlerInterface $handler): RedirectResponse
    {
        $command = new RegisterUserCommand($request->toDTO());

        $result = $handler->handle($command);

        if ($result->failed()) {
            return back()
                ->withInput()
                ->withErrors([
                    'general' => $result->message ?? 'Something went wrong.',
                ]);
        }

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    public function login(): View
    {
        return view("auth.login");
    }

    public function authenticate(LoginRequest $request): RedirectResponse
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

    public function logout(): RedirectResponse
    {

        try {
            $this->userService->logout();

            return redirect()->route('dashboard')->with('success', 'Logged out successfully');
        } catch (Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());  // <- Пределать
            return back()->with('error', 'Logout failed. Please try again.'); // Поправить редирект на такую страницу которая сможет обработать сообщение
        }

    }

}
