<?php

namespace App\Domains\Utils\Enums;

enum WithdrawalStatusEnum: string
{
    case PENDING = 'pending';
    case SUCCESSFUL = 'successful';
    case PROCESSING = 'processing';
    case FAILED = 'failed';
}
