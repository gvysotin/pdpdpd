<?php

// app/Factories/UserFactory.php
namespace App\Factories;

use App\Contracts\UserFactoryInterface;
use App\DataTransferObjects\UserRegistrationData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserFactory implements UserFactoryInterface
{
    public function createFromDTO(UserRegistrationData $data): User
    {
        logger()->debug('Creating user from DTO', [
            'email' => $data->email,
        ]);

        return new User([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password, // Уже хеширован
        ]);
    }
    
}