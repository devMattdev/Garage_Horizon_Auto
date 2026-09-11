<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\DemandeRecueMessage;
use App\Repository\DemandeRepository;
use App\Service\Contact\NotificationDemande;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DemandeRecueMessageHandler
{
    public function __construct(
        private readonly DemandeRepository $demandeRepository,
        private readonly NotificationDemande $notificationDemande,
    ) {
    }

    public function __invoke(DemandeRecueMessage $message): void
    {
        $demande = $this->demandeRepository->find($message->demandeId);

        if (null === $demande) {
            return;
        }

        $this->notificationDemande->notifierGarage($demande);
        $this->notificationDemande->envoyerAccuseReception($demande);
    }
}
