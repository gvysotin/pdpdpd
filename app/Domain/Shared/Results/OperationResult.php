<?php

namespace App\Domain\Shared\Results;

use App\Domain\Shared\Enums\ResultStatus;

final class OperationResult
{
    private function __construct(
        public readonly ResultStatus $status,
        public readonly ?string $message = null,
    ) {}

    public static function success(): self
    {
        return new self(ResultStatus::SUCCESS);
    }

    public static function failure(string $message): self
    {
        return new self(ResultStatus::FAILURE, $message);
    }

    public function succeeded(): bool
    {
        return $this->status === ResultStatus::SUCCESS;
    }

    public function failed(): bool
    {
        return $this->status === ResultStatus::FAILURE;
    }
}