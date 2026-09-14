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

    public function libelle(): string
    {
        return match ($this) {
            self::LUNDI => 'Lundi',
            self::MARDI => 'Mardi',
            self::MERCREDI => 'Mercredi',
            self::JEUDI => 'Jeudi',
            self::VENDREDI => 'Vendredi',
            self::SAMEDI => 'Samedi',
            self::DIMANCHE => 'Dimanche',
        };
    }
}
