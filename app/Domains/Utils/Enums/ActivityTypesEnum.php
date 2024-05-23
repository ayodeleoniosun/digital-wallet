<?php

namespace App\Domains\Utils\Enums;

enum ActivityTypesEnum: string
{
    case REGISTER = 'register';
    case LOGIN = 'login';
    case CREATE_VIRTUAL_ACCOUNT = 'create-virtual-account';
}
