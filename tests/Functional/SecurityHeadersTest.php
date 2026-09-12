<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Utilisateur;

/**
 * En-têtes de sécurité HTTP (back.md section 7.2, risque OWASP A05) : CSP avec
 * nonce, HSTS, X-Content-Type-Options, Referrer-Policy, Permissions-Policy —
 * y compris sur le back-office EasyAdmin, qui consomme automatiquement la
 * fonction Twig "csp_nonce" dès qu'elle est enregistrée.
 */
final class SecurityHeadersTest extends DatabaseWebTestCase
{
    public function testEnTetesPresentsSurUnePagePublique(): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        $headers = $this->client->getResponse()->headers;

        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('strict-origin-when-cross-origin', $headers->get('Referrer-Policy'));
        self::assertStringStartsWith('max-age=31536000', (string) $headers->get('Strict-Transport-Security'));

        $csp = (string) $headers->get('Content-Security-Policy');
        self::assertStringContainsString("frame-ancestors 'self'", $csp);
        self::assertStringContainsString("base-uri 'self'", $csp);

        $nonceScript = $this->extraireNonce($csp, 'script-src');

        // Le module importmap d'AssetMapper est inline : il doit porter le nonce déclaré dans le CSP.
        $importmap = $crawler->filterXPath('//script[@type="importmap"]');
        self::assertGreaterThan(0, $importmap->count());
        self::assertSame($nonceScript, $importmap->attr('nonce'));
    }

    public function testEnTetesPresentsSurLeBackOffice(): void
    {
        $utilisateur = (new Utilisateur())
            ->setEmail('gerant@example.test')
            ->setPassword('mot-de-passe-de-test')
            ->setNom('Gérant Test')
            ->setRoles([Utilisateur::ROLE_ADMIN]);
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();
        $this->client->loginUser($utilisateur, 'admin');

        $crawler = $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        $csp = (string) $this->client->getResponse()->headers->get('Content-Security-Policy');
        $nonceScript = $this->extraireNonce($csp, 'script-src');
        $nonceStyle = $this->extraireNonce($csp, 'style-src');

        // EasyAdmin détecte automatiquement la fonction Twig "csp_nonce" et l'applique
        // à ses propres scripts et feuilles de style (voir vendor easyadmin layout.html.twig).
        $scriptApp = $crawler->filterXPath('//script[contains(@src, "/bundles/easyadmin/app")]');
        self::assertGreaterThan(0, $scriptApp->count());
        self::assertSame($nonceScript, $scriptApp->first()->attr('nonce'));

        $feuilleStyle = $crawler->filterXPath('//link[@rel="stylesheet"][contains(@href, "/bundles/easyadmin/app")]');
        self::assertGreaterThan(0, $feuilleStyle->count());
        self::assertSame($nonceStyle, $feuilleStyle->first()->attr('nonce'));

        $metaNonce = $crawler->filterXPath('//meta[@name="csp-nonce"]');
        self::assertGreaterThan(0, $metaNonce->count());
        self::assertSame($nonceStyle, $metaNonce->attr('content'));
    }

    private function extraireNonce(string $csp, string $directive): string
    {
        $trouve = preg_match('/'.preg_quote($directive, '/')."[^;]*'nonce-([^']+)'/", $csp, $correspondances);
        self::assertSame(1, $trouve, sprintf('Directive "%s" avec nonce introuvable dans : %s', $directive, $csp));

        return $correspondances[1];
    }
}
