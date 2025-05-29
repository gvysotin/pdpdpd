<?php

namespace App\Http\Requests;

use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\ValueObjects\Email;
use App\Domain\Registration\ValueObjects\PlainPassword;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest2 extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
                'max:40',
            ],
            'email' => [
                'required',
                'string',
                'max:256',                
            ],
            'password' => [
                'required',
                'max:256',                
            ],
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
