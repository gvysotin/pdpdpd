<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface EmailNotificationServiceInterface
{
    public function sendWelcomeEmail(User $user): void;

    public function sendVerificationEmail(User $user): void;
}