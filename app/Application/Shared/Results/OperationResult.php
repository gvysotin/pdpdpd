<?php

namespace App\Application\Shared\Results;

use App\Application\Shared\Enums\OperationResultEnum;


final class OperationResult
{
    private function __construct(
        public readonly OperationResultEnum $status,
        public readonly ?string $message = null,
        public readonly mixed $data = null,
    ) {}

    public static function success(mixed $data = null, ?string $message = null): self
    {
        return new self(OperationResultEnum::SUCCESS, $message, $data);
    }

    public static function failure(string $message, mixed $data = null): self
    {
        return new self(OperationResultEnum::FAILURE, $message, $data);
    }

    public function succeeded(): bool
    {
        return $this->status === OperationResultEnum::SUCCESS;
    }

    public function failed(): bool
    {
        return $this->status === OperationResultEnum::FAILURE;
    }

    public function isSuccess(): bool
    {
        return $this->succeeded();
    }

    public function isFailure(): bool
    {
        return $this->failed();
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function data(): mixed
    {
        return $this->data;
    }

}