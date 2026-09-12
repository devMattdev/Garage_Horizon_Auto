<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Génère un nonce CSP par requête et par famille de directive (script/style),
 * mémorisé pour rester identique à chaque appel (back.md section 7.2, A05).
 * EasyAdmin détecte et utilise automatiquement une fonction Twig "csp_nonce"
 * de cette forme dès qu'elle est enregistrée (voir App\Twig\CspExtension).
 */
final class CspNonceGenerator
{
    /**
     * @var array<string, string>
     */
    private array $nonces = [];

    public function generate(string $type = 'script'): string
    {
        return $this->nonces[$type] ??= base64_encode(random_bytes(16));
    }
}
