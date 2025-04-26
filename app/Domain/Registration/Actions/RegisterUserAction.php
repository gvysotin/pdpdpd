<?php

namespace App\Domain\Registration\Actions;

use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Exceptions\UserRegistrationException;
use App\Domain\Shared\Results\OperationResult;
use App\Events\Registration\UserRegistered;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Throwable;


class RegisterUserAction
{
    public function __construct(
        protected UserCreatorInterface $userCreator,
        protected LoggerInterface $logger
    ) {
    }

    public function execute(UserRegistrationData $data): OperationResult
    {
        try {
            $this->logger->info('Starting user registration', [
                'email_hash' => hash('sha256', $data->email),
            ]);

            DB::beginTransaction();

            // Хешируем пароль, создавая новый DTO
            $data = $data->withHashedPassword();

            // Передаём уже хешированный DTO в сервис
            $user = $this->userCreator->create($data);

            // Используем DB::afterCommit() для отложенного выполнения события
            DB::afterCommit(function () use ($user) {
                event(new UserRegistered($user));
            });
            
            DB::commit();

            $this->logger->info('New user registered', [
                'user_id' => $user->id,
                'event_dispatched' => true,
                'source' => 'web', // в будущем можно передавать другое значение, например 'mobile', 'api'
            ]);

            return OperationResult::success();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->logger->error('Registration failed', ['exception' => $e]);
            return OperationResult::failure('Failed to register user.');
        }
    }
}