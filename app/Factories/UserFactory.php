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
            'email' => (string) $data->email,
        ]);

        return new User([
            'name' => $data->name,
            'email' => (string) $data->email,
            'password' => (string) $data->password, // Уже хеширован
        ]);
    }
    
}