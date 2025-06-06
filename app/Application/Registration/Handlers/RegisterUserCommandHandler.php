<?php

namespace App\Application\Registration\Handlers;

use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Shared\Results\OperationResult;
use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserCreatorInterface;
use App\Domain\Shared\Contracts\TransactionManagerInterface;
use App\Domain\Registration\Exceptions\UserRegistrationException;
use App\Events\Registration\UserRegistered;
use Psr\Log\LoggerInterface;
use Throwable;

class RegisterUserCommandHandler
{
    public function __construct(
        private UserCreatorInterface $userCreator,
        private EmailSpecificationInterface $uniqueEmailSpec,
        private LoggerInterface $logger,
        private TransactionManagerInterface $transaction
    ) {
    }

    public function handle(RegisterUserCommand $command): OperationResult
    {
        $data = $command->data;

        try {
            $this->logger->info('Starting user registration', [
                'email_hash' => hash('sha256', $data->email),
            ]);

            // 1. Проверка email ДО транзакции
            $this->uniqueEmailSpec->check($data->email);

            // 2. Начало транзакции                
            $this->transaction->begin();

            // 3. Создание и сохранение пользователя с хэшированнным паролем             
            $user = $this->userCreator->create($data->withHashedPassword());

            // 4. Dispatch события после коммита
            $this->transaction->afterCommit(function () use ($user) {
                event(new UserRegistered($user));
            });

            // 5. Коммит транзакции                            
            $this->transaction->commit();

            $this->logger->info('New user registered', [
                'user_id' => $user->id,
                'event' => UserRegistered::class,
                'source' => 'web',
            ]);

            return OperationResult::success();
        } catch (UserRegistrationException $e) {
            $this->transaction->rollback();

            $this->logger->error('Duplicate email attempt', [
                'exception' => $e->getMessage(),
                'email_hash' => hash('sha256', (string) $data->email),
                'source' => 'web'
            ]);

            return OperationResult::failure($e->getMessage());
        } catch (Throwable $e) {
            $this->transaction->rollback();

            $this->logger->error('User registration failed', [
                'exception' => $e,
                'email_hash' => hash('sha256', (string) $data->email),
                'source' => 'web'
            ]);

            return OperationResult::failure('Failed to register user');
        }
    }
}