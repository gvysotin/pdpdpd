<?php

namespace App\Application\Registration\Contracts;

use App\Domain\Registration\DTO\UserRegistrationData;
use App\Domain\Shared\Results\OperationResult;

interface RegisterUserActionInterface
{
    public function execute(UserRegistrationData $data): OperationResult;
}