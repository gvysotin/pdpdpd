<?php

namespace App\Application\Registration\Contracts;

use App\Application\Registration\Commands\RegisterUserCommand;
use App\Application\Shared\Results\OperationResult;

interface RegisterUserHandlerInterface
{
    public function handle(RegisterUserCommand $command): OperationResult;
}