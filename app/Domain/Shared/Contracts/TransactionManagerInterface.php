<?php
// app/Domain/Shared/Contracts/TransactionManagerInterface.php

namespace App\Domain\Shared\Contracts;

interface TransactionManagerInterface
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;

    public function afterCommit(callable $callback): void;

    public function run(callable $callback): mixed;
}