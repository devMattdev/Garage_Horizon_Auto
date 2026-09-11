<?php

declare(strict_types=1);

namespace App\Enum;

enum FamillePrestation: string
{
    case ENTRETIEN = 'entretien';
    case MECANIQUE = 'mecanique';
    case PNEUMATIQUE = 'pneumatique';
    case CLIMATISATION = 'climatisation';
    case DIAGNOSTIC = 'diagnostic';
    case CONTROLE_TECHNIQUE = 'controle_technique';
}
