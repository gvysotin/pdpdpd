<?php
// app/Infrastructure/Shared/Transaction/LaravelTransactionManager.php

namespace App\Infrastructure\Shared\Transaction;

use App\Domain\Shared\Contracts\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

class LaravelTransactionManager implements TransactionManagerInterface
{
    public function begin(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollback(): void
    {
        DB::rollBack();
    }

    public function afterCommit(callable $callback): void
    {
        DB::afterCommit($callback);
    }

    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}