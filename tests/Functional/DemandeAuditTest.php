<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Demande;
use App\Entity\JournalAudit;
use App\Entity\Utilisateur;
use App\Enum\StatutDemande;
use App\Enum\TypeDemande;

/**
 * back.md section 7.2/17 : chaque consultation d'une Demande par le back-office
 * écrit un JournalAudit (et le canal de log "audit") — vérifie ici que le
 * câblage du logger dédié au canal "audit" (#[Target('audit')]) fonctionne de
 * bout en bout, au-delà des seuls tests unitaires.
 */
final class DemandeAuditTest extends DatabaseWebTestCase
{
    public function testLaConsultationDUneDemandeEcritUnJournalAudit(): void
    {
        $demande = new Demande();
        $demande->setType(TypeDemande::QUESTION);
        $demande->setNom('Client Test');
        $demande->setEmail('client@example.test');
        $demande->setStatut(StatutDemande::NOUVELLE);
        $demande->setPurgePrevueLe(new \DateTimeImmutable('+12 months'));
        $this->entityManager->persist($demande);

        $utilisateur = (new Utilisateur())
            ->setEmail('gerant@example.test')
            ->setPassword('mot-de-passe-de-test')
            ->setNom('Gérant Test')
            ->setRoles([Utilisateur::ROLE_ADMIN]);
        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();
        $this->client->loginUser($utilisateur, 'admin');

        $this->client->request('GET', \sprintf('/admin/demande/%d', $demande->getId()));

        self::assertResponseIsSuccessful();

        $journal = $this->entityManager->getRepository(JournalAudit::class)->findOneBy([
            'entite' => 'Demande',
            'entiteId' => $demande->getId(),
            'action' => 'consultation',
        ]);
        self::assertNotNull($journal);
    }
}
