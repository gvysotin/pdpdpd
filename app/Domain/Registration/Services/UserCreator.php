<?php

namespace App\Domain\Registration\Services;


use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
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