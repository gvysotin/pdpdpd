<?php

// app/DataTransferObjects/UserRegistrationData.php
namespace App\DataTransferObjects;

use DateTimeInterface;

class UserRegistrationData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly ?DateTimeInterface $emailVerifiedAt = null
    ) {}
}