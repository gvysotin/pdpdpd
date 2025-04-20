<?php

// app/Contracts/WelcomeEmailSenderInterface.php
namespace App\Contracts;

use App\Models\User;

interface WelcomeEmailSenderInterface
{
    public function send(User $user): void;
}