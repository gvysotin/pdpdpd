<?php

namespace App\Domain\Registration\Services;


use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Exceptions\UserRegistrationException;
use App\Domain\Registration\Specifications\UniqueEmailSpecification;
use App\Models\User;

class UserCreator implements UserCreatorInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory,
        private readonly UniqueEmailSpecification $uniqueEmailSpec
    ) {}

    public function create(UserRegistrationData $data): User
    {
        if (! $this->uniqueEmailSpec->isSatisfiedBy($data->email)) {
            throw new UserRegistrationException('Email already registered');
        }

        $user = $this->userFactory->createFromDTO($data);

        $user->save();

        return $user;
    }

}