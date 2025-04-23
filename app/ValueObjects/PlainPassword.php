<?php

namespace App\ValueObjects;

use App\ValueObjects\HashedPassword;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

final class PlainPassword
{
    public readonly string $value;

    public function __construct(string $value)
    {
        if (strlen($value) < 8) {
            throw new InvalidArgumentException("Password must be at least 8 characters.");
        }

        $this->value = $value;
    }

    public function hash(): HashedPassword
    {
        return new HashedPassword(Hash::make($this->value));
    }
}