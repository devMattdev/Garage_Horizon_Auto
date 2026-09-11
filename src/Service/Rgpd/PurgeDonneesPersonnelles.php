<?php

declare(strict_types=1);

namespace App\Service\Rgpd;

use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Supprime les demandes dont `purgePrevueLe` est dépassé (RG-09, back.md section 9 et 11).
 */
class PurgeDonneesPersonnelles
{
    public function __construct(
        private readonly DemandeRepository $demandeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function purger(?\DateTimeImmutable $aujourdhui = null): int
    {
        $aujourdhui ??= new \DateTimeImmutable();

        $demandesAPurger = $this->demandeRepository->findAPurger($aujourdhui);

        foreach ($demandesAPurger as $demande) {
            $this->entityManager->remove($demande);
        }

        $this->entityManager->flush();

        $this->logger->info('Purge RGPD : {nombre} demande(s) supprimée(s).', ['nombre' => \count($demandesAPurger)]);

        return \count($demandesAPurger);
    }
}
