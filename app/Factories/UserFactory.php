<?php

// app/Factories/UserFactory.php
namespace App\Factories;

use App\DataTransferObjects\UserRegistrationData;
use App\Models\User;

class UserFactory
{
    public function createFromDTO(UserRegistrationData $data): User
    {
        return new User([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'email_verified_at' => $data->emailVerifiedAt
        ]);
    }

}