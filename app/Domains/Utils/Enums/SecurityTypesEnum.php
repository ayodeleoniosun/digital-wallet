<?php

namespace App\Domains\Utils\Enums;

enum SecurityTypesEnum: string
{
    case TRANSACTION_PIN = 'transaction-pin';
    case GOOGLE_2FA = 'google-2fa';
}
