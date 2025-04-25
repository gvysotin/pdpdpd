<?php

namespace App\Domain\Shared\Enums;

enum ResultStatus: string
{
    case SUCCESS = 'success';
    case FAILURE = 'failure';
}