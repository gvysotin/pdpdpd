<?php

// app/Services/UserCreator.php
namespace App\Services;

use App\Contracts\UserCreatorInterface;
use App\Contracts\UserFactoryInterface;
use App\DataTransferObjects\UserRegistrationData;
use App\Models\User;

class UserCreator implements UserCreatorInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory
    ) {}

    public function create(UserRegistrationData $data): User
    {
        $user = $this->userFactory->createFromDTO($data);
        $user->save();
        return $user;
    }

}