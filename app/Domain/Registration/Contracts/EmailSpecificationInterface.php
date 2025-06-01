<?php

namespace App\Domain\Registration\Contracts;

use App\Domain\Registration\Exceptions\UserRegistrationException;
use App\Domain\Registration\ValueObjects\Email;

interface EmailSpecificationInterface {
    public function emailExists(Email $email): bool;
    
    /** @throws UserRegistrationException */
    public function check(Email $email): void;
}

