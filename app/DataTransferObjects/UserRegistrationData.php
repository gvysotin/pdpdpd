<?php

// app/DataTransferObjects/UserRegistrationData.php
namespace App\DataTransferObjects;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserRegistrationData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password')
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
        return new self(
            name: $this->name,
            email: $this->email,
            password: Hash::make($this->password),
        );
    }

}