<?php

declare(strict_types=1);

namespace App\Enum;

enum Energie: string
{
    case ESSENCE = 'essence';
    case DIESEL = 'diesel';
    case HYBRIDE = 'hybride';
    case ELECTRIQUE = 'electrique';
    case GPL = 'gpl';
}
