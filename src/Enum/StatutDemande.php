<?php

declare(strict_types=1);

namespace App\Enum;

enum StatutDemande: string
{
    case NOUVELLE = 'nouvelle';
    case EN_COURS = 'en_cours';
    case TRAITEE = 'traitee';
    case ARCHIVEE = 'archivee';
}
