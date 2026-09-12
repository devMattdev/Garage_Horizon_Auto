<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Security\CspNonceGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * En-têtes de sécurité HTTP (back.md section 7.2, risque OWASP A05) : CSP, HSTS,
 * X-Content-Type-Options, Referrer-Policy, Permissions-Policy.
 *
 * Le CSP s'appuie sur un nonce par requête (App\Security\CspNonceGenerator) plutôt
 * que sur 'unsafe-inline', y compris pour les scripts et styles générés par
 * EasyAdmin, qui détecte et utilise automatiquement la fonction Twig "csp_nonce".
 */
final class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CspNonceGenerator $cspNonceGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', $this->buildContentSecurityPolicy());
    }

    private function buildContentSecurityPolicy(): string
    {
        $scriptNonce = $this->cspNonceGenerator->generate('script');
        $styleNonce = $this->cspNonceGenerator->generate('style');

        return implode('; ', [
            "default-src 'self'",
            \sprintf("script-src 'self' 'nonce-%s' https://challenges.cloudflare.com", $scriptNonce),
            \sprintf("style-src 'self' 'nonce-%s'", $styleNonce),
            "img-src 'self' data:",
            "font-src 'self'",
            'frame-src https://challenges.cloudflare.com',
            "connect-src 'self' https://challenges.cloudflare.com",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
