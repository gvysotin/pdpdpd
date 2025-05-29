<?php

namespace App\Domain\Shared\Enums;

enum OperationResultEnum: string
{
    case SUCCESS = 'success';
    case FAILURE = 'failure';
}