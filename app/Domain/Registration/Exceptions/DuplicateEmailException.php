<?php

// DuplicateEmailException.php
namespace App\Domain\Registration\Exceptions;

use Throwable;

class DuplicateEmailException extends UserRegistrationException
{
    public function __construct(
        string $message = "Email is already registered",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
