<?php

namespace App\Domain\Registration\DTO;

use App\Domain\Registration\ValueObjects\{Email, HashedPassword, PlainPassword};
use Illuminate\Http\Request;

class UserRegistrationData
{
    public function __construct(
        public readonly string $name,
        public readonly Email $email,
        public readonly PlainPassword|HashedPassword $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            email: new Email($request->input('email')),
            password: new PlainPassword($request->input('password')),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password']
        );
    }

    public function withHashedPassword(): self
    {
        if ($this->password instanceof HashedPassword) {
            return $this; // Уже хеширован
        }

        return new self(
            name: $this->name,
            email: $this->email,
            password: $this->password->hash()
        );
    }

}