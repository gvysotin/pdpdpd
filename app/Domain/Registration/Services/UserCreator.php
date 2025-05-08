<?php

namespace App\Domain\Registration\Services;

use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Models\User;

class UserCreator implements UserCreatorInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory,
        private readonly EmailSpecificationInterface $uniqueEmailSpec
    ) {}

    public function create(UserRegistrationData $data): User
    {
        $this->uniqueEmailSpec->check($data->email); // Выбрасывает исключение        

        $user = $this->userFactory->createFromDTO($data);

        $user->save();

        return $user;
    }

}