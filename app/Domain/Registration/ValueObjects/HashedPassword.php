<?php

namespace App\Domain\Registration\ValueObjects;

use InvalidArgumentException;

final class HashedPassword
{
    public readonly string $value;

    public function __construct(string $value)
    {

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}