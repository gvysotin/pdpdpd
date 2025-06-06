<?php

namespace App\Domain\Registration\Specifications;

use App\Domain\Registration\Contracts\EmailSpecificationInterface;
use App\Domain\Registration\Contracts\UserRepositoryInterface;
use App\Domain\Registration\Exceptions\DuplicateEmailException;
use App\Domain\Registration\ValueObjects\Email;

class UniqueEmailSpecification implements EmailSpecificationInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function emailExists(Email $email): bool
    {
        return $this->userRepository->emailExists($email);
    }

    public function check(Email $email): void
    {
        if ($this->emailExists($email)) {
            throw new DuplicateEmailException('Email already registered');
        }
    }
    
}