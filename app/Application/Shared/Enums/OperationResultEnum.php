<?php

namespace App\Application\Shared\Enums;

enum OperationResultEnum: string
{
    case SUCCESS = 'success';
    case FAILURE = 'failure';
}