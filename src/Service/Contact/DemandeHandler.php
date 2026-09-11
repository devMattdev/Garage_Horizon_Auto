<?php

declare(strict_types=1);

namespace App\Service\Contact;

use App\Entity\Demande;
use App\Message\DemandeRecueMessage;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Valide, déduplique, persiste et déclenche les notifications d'une demande
 * (back.md section 9, RG-01, RG-09, RG-12).
 */
class DemandeHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DemandeRepository $demandeRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly int $conservationMois,
    ) {
    }

    /**
     * @return array{demande: Demande, doublon: bool}
     */
    public function traiter(Demande $demande): array
    {
        $demande->setPurgePrevueLe(
            (new \DateTimeImmutable())->modify(\sprintf('+%d months', $this->conservationMois)),
        );

        if (null !== $demande->getImmatriculation()) {
            $demande->setImmatriculation(
                strtoupper(str_replace(' ', '', $demande->getImmatriculation())),
            );
        }

        if (null !== $demande->getEmail()) {
            $doublon = $this->demandeRepository->trouverDoublonRecent(
                $demande->getEmail(),
                $demande->getType(),
                $demande->getMessage(),
                (new \DateTimeImmutable())->modify('-5 minutes'),
            );

            if (null !== $doublon) {
                return ['demande' => $doublon, 'doublon' => true];
            }
        }

        $this->entityManager->persist($demande);
        $this->entityManager->flush();

        $demandeId = $demande->getId();
        if (null === $demandeId) {
            throw new \LogicException('La demande devrait avoir un identifiant après le flush.');
        }

        $this->messageBus->dispatch(new DemandeRecueMessage($demandeId));

        return ['demande' => $demande, 'doublon' => false];
    }
}
