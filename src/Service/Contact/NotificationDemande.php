<?php

declare(strict_types=1);

namespace App\Service\Contact;

use App\Entity\Demande;
use App\Repository\ParametresSiteRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Compose et envoie le courriel interne et l'accusé de réception (back.md section 9 et 13).
 */
class NotificationDemande
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ParametresSiteRepository $parametresSiteRepository,
        private readonly string $adresseExpediteur,
    ) {
    }

    public function notifierGarage(Demande $demande): void
    {
        $parametres = $this->parametresSiteRepository->getInstance();

        $email = (new TemplatedEmail())
            ->from(new Address($this->adresseExpediteur, 'Horizon Auto — site web'))
            ->to($parametres->getEmail() ?: $this->adresseExpediteur)
            ->subject(\sprintf('Nouvelle demande — %s', $demande->getType()->value))
            ->htmlTemplate('emails/demande_garage.html.twig')
            ->context(['demande' => $demande]);

        $this->mailer->send($email);
    }

    public function envoyerAccuseReception(Demande $demande): void
    {
        if (null === $demande->getEmail()) {
            return;
        }

        $parametres = $this->parametresSiteRepository->getInstance();

        $email = (new TemplatedEmail())
            ->from(new Address($this->adresseExpediteur, 'Horizon Auto'))
            ->to($demande->getEmail())
            ->subject('Votre demande a bien été reçue — Horizon Auto')
            ->htmlTemplate('emails/demande_accuse_reception.html.twig')
            ->context([
                'demande' => $demande,
                'parametres' => $parametres,
            ]);

        $this->mailer->send($email);
    }
}
