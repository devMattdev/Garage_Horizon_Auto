<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Utilisateur;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Section 8 (back.md) : plusieurs écrans du back-office (Prestations, FAQ, Pages
 * locales, Fermetures, Utilisateurs) sont réservés au Gérant, à la différence des
 * Demandes qui restent accessibles au Secrétariat.
 */
final class EasyAdminAccesGerantTest extends DatabaseWebTestCase
{
    private function creerUtilisateur(string $role): Utilisateur
    {
        $utilisateur = (new Utilisateur())
            ->setEmail(sprintf('%s@example.test', strtolower($role)))
            ->setPassword('mot-de-passe-de-test')
            ->setNom('Utilisateur de test')
            ->setRoles([$role]);
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        return $utilisateur;
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEcransReservesAuGerant(): iterable
    {
        yield 'Prestations' => ['/admin/prestation'];
        yield 'FAQ' => ['/admin/question-frequente'];
        yield 'Pages locales' => ['/admin/page-locale'];
        yield 'Fermetures exceptionnelles' => ['/admin/fermeture-exceptionnelle'];
        yield 'Utilisateurs' => ['/admin/utilisateur'];
        yield 'Horaires' => ['/admin/horaires'];
        yield 'Paramètres du site' => ['/admin/parametres'];
        yield 'Export RGPD' => ['/admin/rgpd/export'];
    }

    #[DataProvider('provideEcransReservesAuGerant')]
    public function testUnSecretariatNAccedePasAUnEcranReserveAuGerant(string $chemin): void
    {
        $utilisateur = $this->creerUtilisateur(Utilisateur::ROLE_SECRETARIAT);
        $this->client->loginUser($utilisateur, 'admin');

        $this->client->request('GET', $chemin);

        self::assertResponseStatusCodeSame(403);
    }

    #[DataProvider('provideEcransReservesAuGerant')]
    public function testUnGerantAccedeAUnEcranReserveAuGerant(string $chemin): void
    {
        $utilisateur = $this->creerUtilisateur(Utilisateur::ROLE_ADMIN);
        $this->client->loginUser($utilisateur, 'admin');

        $this->client->request('GET', $chemin);

        self::assertResponseIsSuccessful();
    }

    public function testUnSecretariatAccedeAuxDemandes(): void
    {
        $utilisateur = $this->creerUtilisateur(Utilisateur::ROLE_SECRETARIAT);
        $this->client->loginUser($utilisateur, 'admin');

        $this->client->request('GET', '/admin/demande');

        self::assertResponseIsSuccessful();
    }
}
