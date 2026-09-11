<?php

declare(strict_types=1);

namespace App\Enum;

enum TypeDemande: string
{
    case RDV = 'rdv';
    case DEVIS = 'devis';
    case QUESTION = 'question';
    case VEHICULE = 'vehicule';
}
