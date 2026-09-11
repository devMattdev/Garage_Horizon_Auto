<?php

declare(strict_types=1);

namespace App\Enum;

enum BoiteVitesses: string
{
    case MANUELLE = 'manuelle';
    case AUTOMATIQUE = 'automatique';
}
