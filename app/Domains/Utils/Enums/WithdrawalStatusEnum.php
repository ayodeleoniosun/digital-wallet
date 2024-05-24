<?php

namespace App\Domains\Utils\Enums;

enum WithdrawalStatusEnum: string
{
    case PENDING = '0';
    case SUCCESSFUL = '1';
    case PROCESSING = '2';
    case FAILED = '3';
}
