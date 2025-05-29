<?php

namespace Tests\Unit\Domain\Shared\Results;

use App\Domain\Shared\Enums\OperationResultEnum;
use App\Domain\Shared\Results\OperationResult;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperationResultTest extends TestCase
{
    #[Test]
    public function it_creates_success_result_with_optional_message_and_data(): void
    {
        $data = ['id' => 123];
        $message = 'User registered';

        $result = OperationResult::success($data, $message);

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
        $this->assertEquals(OperationResultEnum::SUCCESS, $result->status);
        $this->assertEquals($message, $result->message());
        $this->assertEquals($data, $result->data());
    }

    #[Test]
    public function it_creates_failure_result_with_optional_data(): void
    {
        $message = 'Validation failed';
        $data = ['email' => 'invalid'];

        $result = OperationResult::failure($message, $data);

        $this->assertTrue($result->isFailure());
        $this->assertFalse($result->isSuccess());
        $this->assertEquals(OperationResultEnum::FAILURE, $result->status);
        $this->assertEquals($message, $result->message());
        $this->assertEquals($data, $result->data());
    }

    #[Test]
    public function it_handles_null_data_on_success_and_failure(): void
    {
        $success = OperationResult::success(null, 'Success message');
        $failure = OperationResult::failure('Failure message');

        $this->assertNull($success->data());
        $this->assertNull($failure->data());
        $this->assertEquals('Success message', $success->message());
        $this->assertEquals('Failure message', $failure->message());
    }
}
