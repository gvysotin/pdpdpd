<?php 

namespace App\Application\Registration\Commands;

use App\Domain\Registration\DTO\UserRegistrationData;

class RegisterUserCommand
{
    public function __construct(
        public readonly UserRegistrationData $data
    ) {}
}