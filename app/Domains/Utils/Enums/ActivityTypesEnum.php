<?php

namespace App\Domains\Utils\Enums;

enum ActivityTypesEnum: string
{
    case REGISTER = 'register';
    case LOGIN = 'login';
    case CREATE_VIRTUAL_ACCOUNT = 'create-virtual-account';
    case FAILED_DEPOSIT = 'failed-deposit';
    case INVALID_DEPOSIT_REFERENCE = 'invalid-deposit-reference';
    case DEPOSIT_ALREADY_EXIST = 'deposit-already-exist';
    case DEPOSIT_COMPLETED = 'deposit-completed';
    case PAYMENT_OPTION_CREATED = 'payment-option-created';
    case PAYMENT_OPTION_DELETED = 'payment-option-deleted';
}
