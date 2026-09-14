<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Utilisateur;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * back.md section 12 : Cache-Control d'une heure sur les pages éditoriales
 * publiques, jamais sur une page portant un formulaire ou une session active.
 */
final class CacheEditorialTest extends DatabaseWebTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function provideRoutesEditoriales(): iterable
    {
        yield 'Accueil' => ['/'];
        yield 'Prestations' => ['/prestations'];
        yield 'FAQ' => ['/questions-frequentes'];
        yield 'Véhicules' => ['/vehicules-occasion'];
    }

    #[DataProvider('provideRoutesEditoriales')]
    public function testCacheHttpSurUnePageEditorialeSansSession(string $chemin): void
    {
        $this->client->request('GET', $chemin);

        self::assertResponseIsSuccessful();
        $cacheControl = (string) $this->client->getResponse()->headers->get('Cache-Control');
        self::assertStringContainsString('public', $cacheControl);
        self::assertStringContainsString('max-age=3600', $cacheControl);
    }

    public function testAucunCacheSurLeFormulaireDeContact(): void
    {
        $this->client->request('GET', '/rendez-vous');

        self::assertResponseIsSuccessful();
        $cacheControl = (string) $this->client->getResponse()->headers->get('Cache-Control');
        self::assertStringNotContainsString('public', $cacheControl);
    }

    public function testAucunCacheSurUnePageEditorialeAvecSessionActive(): void
    {
        $utilisateur = (new Utilisateur())
            ->setEmail('gerant@example.test')
            ->setPassword('mot-de-passe-de-test')
            ->setNom('Gérant Test')
            ->setRoles([Utilisateur::ROLE_ADMIN]);
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();
        $this->client->loginUser($utilisateur, 'admin');

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        $cacheControl = (string) $this->client->getResponse()->headers->get('Cache-Control');
        self::assertStringNotContainsString('public', $cacheControl);
    }
}
