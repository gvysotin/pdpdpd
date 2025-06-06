<?php

// UserPersistenceException.php
namespace App\Domain\Registration\Exceptions;

use Throwable;

// Возникает, если возникла проблема с сохранением пользователя в базу данных
class UserPersistenceException extends UserRegistrationException
{
    public function __construct(
        string $message = "Could not save user",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
