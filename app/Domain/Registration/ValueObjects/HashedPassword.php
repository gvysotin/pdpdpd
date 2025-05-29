<?php

namespace App\Domain\Registration\ValueObjects;

use InvalidArgumentException;

final class HashedPassword
{
    public readonly string $value;

    public function __construct(string $value)
    {
        $this->validate($value);        
        $this->value = $value;
    }

    private function validate(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException("Hashed password cannot be empty.");
        }

        if (strlen($value) < 60) { // Минимальная длина bcrypt хеша
            throw new InvalidArgumentException("Invalid hash format.");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }


}