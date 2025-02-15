<?php
namespace App\Enum;

enum StatutEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case VALIDE = 'valide';
    case REFUSE = 'refuse';
}
