<?php

// DuplicateEmailException.php
namespace App\Domain\Registration\Exceptions;

class DuplicateEmailException extends UserRegistrationException 
{
    public function __construct(string $message = "Email is already registered")
    {
        parent::__construct($message);
    }    
}
