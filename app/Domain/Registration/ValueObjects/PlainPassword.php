<?php

namespace App\Domain\Registration\ValueObjects;

use App\Domain\Registration\ValueObjects\HashedPassword;
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

        if (strlen($value) > 256) {
            throw new InvalidArgumentException("Password must be at most 256 characters.");
        }

        $this->value = $value;
    }

    public function hash(): HashedPassword
    {
        return new HashedPassword(Hash::make($this->value));
    }
}