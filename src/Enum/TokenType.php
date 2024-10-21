<?php

namespace App\Enum;

enum TokenType: string
{
    case ACCESS = 'access';
    case REFRESH = 'refresh';
}
