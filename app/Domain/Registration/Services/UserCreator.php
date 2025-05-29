<?php

namespace App\Domain\Registration\Services;

use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\Contracts\UserFactoryInterface;
use App\Domain\Registration\Contracts\UserRepositoryInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Exceptions\UserRegistrationException;
use App\Models\User;
use Throwable;

class UserCreator implements UserCreatorInterface
{
    public function __construct(
        private UserFactoryInterface $userFactory,
        private readonly EmailSpecificationInterface $uniqueEmailSpec,
        private UserRepositoryInterface $userRepository        
    ) {}

    public function create(UserRegistrationData $data): User
    {
        $this->uniqueEmailSpec->check($data->email); // Выбрасывает исключение        

        $user = $this->userFactory->createFromDTO($data);

        try {
            $this->userRepository->save($user);
        } catch (Throwable $e) {
            throw new UserRegistrationException('Could not save user', 0, $e);
        }

        return $user;
    }

}