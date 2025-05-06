<?php

namespace App\Domain\Specifications;

use App\Domain\Registration\ValueObjects\Email;
use App\Models\User;

class UniqueEmailSpecification
{
    public function isSatisfiedBy(Email $email): bool
    {
        return !User::where('email', (string)$email)->exists();
    }
}