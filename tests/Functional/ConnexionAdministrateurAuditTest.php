<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\JournalAudit;
use App\Entity\Utilisateur;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * back.md section 10/17 : une connexion réussie au back-office écrit un
 * JournalAudit et une entrée sur le canal de log "audit". `loginUser()` (raccourci
 * de test) ne déclenche pas LoginSuccessEvent : on passe donc par une vraie
 * soumission du formulaire de connexion.
 */
final class ConnexionAdministrateurAuditTest extends DatabaseWebTestCase
{
    public function testUneConnexionReussieEcritUnJournalAudit(): void
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertInstanceOf(UserPasswordHasherInterface::class, $hasher);

        $utilisateur = (new Utilisateur())
            ->setEmail('gerant@example.test')
            ->setNom('Gérant Test')
            ->setRoles([Utilisateur::ROLE_ADMIN]);
        $utilisateur->setPassword($hasher->hashPassword($utilisateur, 'mot-de-passe-de-test-1234'));
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();
        $utilisateurId = $utilisateur->getId();

        $crawler = $this->client->request('GET', '/admin/connexion');
        $form = $crawler->filterXPath('//form')->form([
            '_username' => 'gerant@example.test',
            '_password' => 'mot-de-passe-de-test-1234',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/admin');

        $journal = $this->entityManager->getRepository(JournalAudit::class)->findOneBy([
            'entite' => 'Utilisateur',
            'entiteId' => $utilisateurId,
            'action' => 'connexion',
        ]);
        self::assertNotNull($journal);
    }
}
