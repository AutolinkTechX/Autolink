<?php

namespace App\Enum;

enum Role: string
{
    case Client = 'ROLE_CLIENT';
    case Admin = 'ROLE_ADMIN';
    case Entreprise = 'ROLE_ENTREPRISE';
}
