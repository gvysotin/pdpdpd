<?php

namespace App\Domain\Registration\Factories;

use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
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