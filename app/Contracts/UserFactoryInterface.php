<?php

// app/Contracts/UserFactoryInterface.php
namespace App\Contracts;

use App\DataTransferObjects\UserRegistrationData;
use App\Models\User;

interface UserFactoryInterface
{
    public function createFromDTO(UserRegistrationData $data): User;
}
