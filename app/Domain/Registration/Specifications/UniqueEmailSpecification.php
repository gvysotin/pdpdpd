<?php

namespace App\Domain\Registration\Specifications;

use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Exceptions\UserRegistrationException;
use App\Domain\Registration\ValueObjects\Email;
use App\Models\User;

class UniqueEmailSpecification implements EmailSpecificationInterface
{
    public function isSatisfiedBy(Email $email): bool
    {
        return !User::where('email', (string)$email)->exists();
    }

    public function check(Email $email): void
    {
        if (!$this->isSatisfiedBy($email)) {
            throw new UserRegistrationException('Email already registered');
        }
    }
    
}