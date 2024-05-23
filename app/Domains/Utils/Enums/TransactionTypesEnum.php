<?php

namespace App\Domains\Utils\Enums;

enum TransactionTypesEnum: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
}
