<?php

// app/Infrastructure/Registration/Repositories/EloquentUserRepository.php

namespace App\Infrastructure\Registration\Repositories;

use App\Domain\Registration\Contracts\UserRepositoryInterface;
use App\Domain\Registration\ValueObjects\Email;
use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByEmail(Email $email): ?User
    {
        return User::where('email', (string)$email)->first();
    }

    public function emailExists(Email $email): bool
    {
        return User::where('email', (string)$email)->exists();
    }

    public function save(User $user): void
    {
        $user->save(); // выбросит исключение, если неудачно
    }

}