<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * RG-02 (back.md section 6.3) : si renseignée, l'immatriculation doit correspondre
 * au format SIV (AA-123-AA) ou à l'ancien format FNI (1234 AB 75).
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Immatriculation extends Constraint
{
    public string $message = 'Cette immatriculation ne correspond à aucun format valide.';
}
