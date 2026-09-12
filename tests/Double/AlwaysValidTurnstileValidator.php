<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Service\Antiabus\TurnstileValidator;

/**
 * Double strictement limite a l'environnement de test. Le jeton reste obligatoire
 * afin que les parcours E2E exercent la meme branche que la production.
 */
final class AlwaysValidTurnstileValidator extends TurnstileValidator
{
    public function __construct()
    {
    }

    public function estValide(?string $jeton, ?string $adresseIp): bool
    {
        return 'jeton-e2e' === $jeton;
    }
}
