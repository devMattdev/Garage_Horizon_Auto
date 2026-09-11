<?php

declare(strict_types=1);

namespace App\Service\Antiabus;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Vérifie côté serveur un jeton Cloudflare Turnstile (back.md section 7.3).
 */
class TurnstileValidator
{
    private const URL_VERIFICATION = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $secretKey,
    ) {
    }

    public function estValide(?string $jeton, ?string $adresseIp): bool
    {
        if ('' === $this->secretKey || null === $jeton || '' === $jeton) {
            return false;
        }

        try {
            $reponse = $this->httpClient->request('POST', self::URL_VERIFICATION, [
                'body' => [
                    'secret' => $this->secretKey,
                    'response' => $jeton,
                    'remoteip' => $adresseIp,
                ],
            ]);

            return (bool) ($reponse->toArray(false)['success'] ?? false);
        } catch (\Throwable) {
            return false;
        }
    }
}
