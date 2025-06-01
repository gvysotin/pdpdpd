<?php

namespace App\Application\Registration\Contracts;

use App\Application\Shared\Results\OperationResult;
use App\Domain\Registration\DTO\UserRegistrationData;

interface RegisterUserActionInterface
{
    public function execute(UserRegistrationData $data): OperationResult;
}