<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Utilisateur;

final class SecurityControllerTest extends DatabaseWebTestCase
{
    public function testRedirigeUnVisiteurAnonymeVersLaConnexion(): void
    {
        $this->client->request('GET', '/admin');

        self::assertResponseRedirects('http://localhost/admin/connexion');
    }

    public function testAutoriseUnAdministrateurConnecte(): void
    {
        $utilisateur = (new Utilisateur())
            ->setEmail('admin@example.test')
            ->setPassword('mot-de-passe-de-test')
            ->setNom('Administrateur')
            ->setRoles([Utilisateur::ROLE_ADMIN]);
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();
        $this->client->loginUser($utilisateur, 'admin');

        $crawler = $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Tableau de bord', $crawler->filterXPath('//h1')->text());
    }
}
