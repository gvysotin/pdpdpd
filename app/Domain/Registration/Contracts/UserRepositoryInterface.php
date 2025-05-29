<?php

// app/Domain/Registration/Contracts/UserRepositoryInterface.php

namespace App\Domain\Registration\Contracts;

use App\Domain\Registration\ValueObjects\Email;
use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(Email $email): ?User;
    public function emailExists(Email $email): bool;
    public function save(User $user): void;
    public function create(array $attributes): User;
}