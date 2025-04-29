<?php

namespace Tests\Unit\Domain\Shared\Results;

use App\Domain\Shared\Enums\OperationResultEnum;
use App\Domain\Shared\Results\OperationResult;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperationResultTest extends TestCase
{
    #[Test]
    public function it_creates_success_result_with_data_and_message(): void
    {
        $result = OperationResult::success(['id' => 123], 'User registered');

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
        $this->assertEquals('User registered', $result->message());
        $this->assertEquals(['id' => 123], $result->data());
        $this->assertEquals(OperationResultEnum::SUCCESS, $result->status);
    }

    #[Test]
    public function it_creates_failure_result_with_message_and_data(): void
    {
        $result = OperationResult::failure('Validation failed', ['email' => 'invalid']);

        $this->assertTrue($result->isFailure());
        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Validation failed', $result->message());
        $this->assertEquals(['email' => 'invalid'], $result->data());
        $this->assertEquals(OperationResultEnum::FAILURE, $result->status);
    }
}
