<?php

namespace App\Actions;

use App\Contracts\UserCreatorInterface;
use App\DataTransferObjects\UserRegistrationData;
use App\Events\UserRegistered;
use App\Exceptions\UserRegistrationException;
use App\Services\UserCreator;
use Psr\Log\LoggerInterface;
use Throwable;

class RegisterUserAction
{
    public function __construct(
        protected UserCreatorInterface $userCreator,
        protected LoggerInterface $logger
    ) {}

    public function execute(UserRegistrationData $data): void
    {
        try {
            $user = $this->userCreator->create($data);

            $this->logger->info('New user registered', [
                'user_id' => $user->id,
                'event_dispatched' => true,          
                'source' => 'web', // в будущем можно передавать другое значение, например 'mobile', 'api'
            ]);

            event(new UserRegistered($user));
        } catch (Throwable $e) {
            $this->logger->error('Registration failed', ['exception' => $e]);
            throw new UserRegistrationException('Failed to register user.', 0, $e);
        }
    }
}