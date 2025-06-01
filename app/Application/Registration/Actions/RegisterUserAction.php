<?php

namespace App\Application\Registration\Actions;

use App\Application\Registration\Contracts\RegisterUserActionInterface;
use App\Application\Shared\Results\OperationResult;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Registration\Exceptions\UserRegistrationException;
use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Events\Registration\UserRegistered;
use Psr\Log\LoggerInterface;
use Throwable;

class RegisterUserAction implements RegisterUserActionInterface
{
    public function __construct(
        protected UserCreatorInterface $userCreator,
        protected LoggerInterface $logger,
        protected TransactionManagerInterface $transaction        
    ) {
    }

    public function execute(UserRegistrationData $data): OperationResult
    {
        try {
            $this->logger->info('Starting user registration', [
                'email_hash' => hash('sha256', $data->email),
            ]);

            $this->transaction->begin();

            $user = $this->userCreator->create($data->withHashedPassword());

            // Используем DB::afterCommit() для отложенного выполнения события
            $this->transaction->afterCommit(function () use ($user) {
                event(new UserRegistered($user));
            });

            $this->transaction->commit();

            $this->logger->info('New user registered', [
                'user_id' => $user->id,
                'event_dispatched' => true,
                'event' => UserRegistered::class,
                'source' => 'web', // в будущем можно передавать другое значение, например 'mobile', 'api'
            ]);

            return OperationResult::success();
        } catch (UserRegistrationException $e) {

            $this->transaction->rollback(); // Откат при бизнес-ошибке      

            $this->logger->error('Duplicate email attempt', [
                'exception' => $e->getMessage(),                
                'email_hash' => hash('sha256', $data->email),
                'source' => 'web'                
            ]);

            return OperationResult::failure($e->getMessage());
        } catch (Throwable $e) {

            $this->transaction->rollback(); // Откат при системной ошибке

            $this->logger->error('User registration failed', [
                'exception' => $e,
                'email_hash' => hash('sha256', (string) $data->email),
                'source' => 'web'
            ]);

            return OperationResult::failure('Failed to register user');
        }
    }
}