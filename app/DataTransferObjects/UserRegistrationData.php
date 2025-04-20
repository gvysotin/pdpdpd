<?php

// app/DataTransferObjects/UserRegistrationData.php
namespace App\DataTransferObjects;

class UserRegistrationData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password
    ) {}
}