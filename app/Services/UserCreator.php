<?php

// app/Services/UserCreator.php
namespace App\Services;

use App\Contracts\UserCreatorInterface;
use App\DataTransferObjects\UserRegistrationData;
use App\Factories\UserFactory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserCreator implements UserCreatorInterface
{
    public function __construct(
        private UserFactory $userFactory
    ) {}

    public function create(UserRegistrationData $data): User
    {

        // Создаём модифицированный DTO с хешированным паролем
        $userData = new UserRegistrationData(
            name: $data->name,
            email: $data->email,
            password: Hash::make($data->password),
            emailVerifiedAt: null,
        );

        $user = $this->userFactory->createFromDTO($userData);
        $user->save();
        
        return $user;
    }

}