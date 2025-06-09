<?php

namespace App\Domain\Registration\Services;

use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\Contracts\UserRepositoryInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Exceptions\UserPersistenceException;
use App\Models\User;
use Throwable;

class UserCreator implements UserCreatorInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function create(UserRegistrationData $data): User
    {
        $user = $this->userFactory->createFromDTO($data);

        try {
            $this->userRepository->save($user);
        } catch (Throwable $e) {
            throw new UserPersistenceException($e);
        }

        return $user;
    }

}