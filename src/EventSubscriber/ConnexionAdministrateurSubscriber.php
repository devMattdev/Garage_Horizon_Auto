<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\JournalAudit;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Écrit un JournalAudit à chaque connexion réussie au back-office (règle métier « ConnexionAdministrateur », back.md section 10).
 */
class ConnexionAdministrateurSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
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
    }
}
