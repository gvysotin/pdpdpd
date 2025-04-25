<?php

namespace App\Domain\Registration\Contracts;

use App\Domain\Registration\DTO\UserRegistrationData;
use App\Models\User;

interface UserFactoryInterface
{
    public function createFromDTO(UserRegistrationData $data): User;
}
