<?php

namespace App\Actions;

use App\Events\UserRegistered;
use App\Exceptions\UserRegistrationException;
use App\Services\UserCreator;
use Psr\Log\LoggerInterface;
use Throwable;

class RegisterUserAction
{
    public function __construct(
        protected UserCreator $userCreator,
        protected LoggerInterface $logger
    ) {}

    public function execute(array $data): void
    {
        try {
            $user = $this->userCreator->create($data);

            //$this->logger->info('Account created successfully!');

            event(new UserRegistered($user));
        } catch (Throwable $e) {
            $this->logger->error('Registration failed', ['exception' => $e]);
            throw new UserRegistrationException('Failed to register user.');
        }
    }
}