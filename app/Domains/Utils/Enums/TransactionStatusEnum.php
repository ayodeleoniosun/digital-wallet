<?php

namespace App\Domains\Utils\Enums;

enum TransactionStatusEnum: string
{
    case SUCCESSFUL = 'successful';
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case FAILED = 'failed';
}
