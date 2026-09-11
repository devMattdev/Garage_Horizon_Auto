<?php

declare(strict_types=1);

namespace App\Enum;

enum JourSemaine: int
{
    case LUNDI = 1;
    case MARDI = 2;
    case MERCREDI = 3;
    case JEUDI = 4;
    case VENDREDI = 5;
    case SAMEDI = 6;
    case DIMANCHE = 7;
}
