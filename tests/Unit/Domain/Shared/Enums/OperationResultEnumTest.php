<?php

namespace Tests\Unit\Domain\Shared\Enums;

use App\Application\Shared\Enums\OperationResultEnum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperationResultEnumTest extends TestCase
{
    #[Test]
    public function it_has_success_and_failure_cases(): void
    {
        $this->assertSame('success', OperationResultEnum::SUCCESS->value);
        $this->assertSame('failure', OperationResultEnum::FAILURE->value);
    }
}
