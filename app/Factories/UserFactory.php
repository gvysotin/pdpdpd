<?php

// app/Factories/UserFactory.php
namespace App\Factories;

use App\DataTransferObjects\UserRegistrationData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserFactory
{
    public function createFromDTO(UserRegistrationData $data): User
    {
        return new User([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $this->hashPassword($data->password), // Уже хешировано в сервисе
            'email_verified_at' => now()
        ]);
    }


    private function hashPassword(string $password): string
    {
        return Hash::make($password);
    }    

}