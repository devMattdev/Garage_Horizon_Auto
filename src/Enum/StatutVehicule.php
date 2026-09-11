<?php

declare(strict_types=1);

namespace App\Enum;

enum StatutVehicule: string
{
    case BROUILLON = 'brouillon';
    case PUBLIE = 'publie';
    case VENDU = 'vendu';
    case RETIRE = 'retire';
}
