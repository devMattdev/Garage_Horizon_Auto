<?php

declare(strict_types=1);

namespace App\Enum;

enum NiveauBandeau: string
{
    case INFO = 'info';
    case ATTENTION = 'attention';
}
