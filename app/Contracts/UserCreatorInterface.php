<?php

// app/Contracts/UserCreatorInterface.php
namespace App\Contracts;

use App\DataTransferObjects\UserRegistrationData;
use App\Models\User;

interface UserCreatorInterface
{
    public function create(UserRegistrationData $data): User;
}