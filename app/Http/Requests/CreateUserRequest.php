<?php

namespace App\Http\Requests;

use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // В маршруте 'middleware' => 'guest', 'throttle:registration'
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
        ];
    }

    /**
     * Custom message for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Please fill in your email address.',
            'email.string' => 'The email must be a string.',
            'email.email' => 'The email must be a correct email address.',
            'email.regex' => 'The email must contain only Latin characters and comply with the format.',
            'email.unique' => 'This email is already registered.',
        ];
    }

    public function toDTO(): UserRegistrationData
    {
        $validated = $this->validated();

        return new UserRegistrationData(
            name: $validated['name'],
            email: new Email($validated['email']),
            password: new PlainPassword($validated['password']),
        );
    }

}
