<?php

// app/Factories/UserFactory.php
namespace App\Factories;

use App\Contracts\UserFactoryInterface;
use App\DataTransferObjects\UserRegistrationData;
use App\Models\User;

class UserFactory implements UserFactoryInterface
{
    public function createFromDTO(UserRegistrationData $data): User
    {
        logger()->debug('Creating user from DTO', [
            'email' => $data->email,
            'verified' => $data->emailVerifiedAt !== null
        ]);

        return new User([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'email_verified_at' => $data->emailVerifiedAt
        ]);
    }

}