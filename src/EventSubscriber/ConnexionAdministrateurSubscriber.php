<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\JournalAudit;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Écrit un JournalAudit à chaque connexion réussie au back-office (règle métier « ConnexionAdministrateur », back.md section 10).
 * Double-écriture sur le canal de log "audit" (back.md section 17) : jamais de
 * courriel ni d'autre donnée personnelle, seulement l'identifiant de l'entité.
 */
class ConnexionAdministrateurSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Target('audit')]
        private readonly LoggerInterface $auditLogger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $utilisateur = $event->getUser();

        if (!$utilisateur instanceof Utilisateur) {
            return;
        }

        $utilisateur->setDerniereConnexionLe(new \DateTimeImmutable());

        $journal = new JournalAudit();
        $journal->setUtilisateur($utilisateur);
        $journal->setAction('connexion');
        $journal->setEntite('Utilisateur');
        $journal->setEntiteId($utilisateur->getId());

        $this->entityManager->persist($journal);
        $this->entityManager->flush();

        $this->auditLogger->info('connexion', ['utilisateur' => $utilisateur->getId()]);
    }
}
