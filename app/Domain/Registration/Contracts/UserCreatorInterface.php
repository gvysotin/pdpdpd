<?php

namespace App\Domain\Registration\Contracts;

use App\Domain\Registration\DTO\UserRegistrationData;
use App\Models\User;

interface UserCreatorInterface
{
    public function create(UserRegistrationData $data): User;
}