<?php

namespace App\Domain\Registration\Contracts;

use App\Models\User;

interface EmailNotificationServiceInterface
{
    public function sendWelcomeEmail(User $user): void;

    public function sendVerificationEmail(User $user): void;
}