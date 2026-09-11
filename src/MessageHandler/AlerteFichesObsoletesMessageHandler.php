<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\AlerteFichesObsoletesMessage;
use App\Repository\ParametresSiteRepository;
use App\Service\Vehicule\PublicationVehicule;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;

/**
 * RG-06 : alerte hebdomadaire au gérant sur les fiches VO potentiellement obsolètes
 * (back.md section 13).
 */
#[AsMessageHandler]
final class AlerteFichesObsoletesMessageHandler
{
    public function __construct(
        private readonly PublicationVehicule $publicationVehicule,
        private readonly ParametresSiteRepository $parametresSiteRepository,
        private readonly MailerInterface $mailer,
        private readonly string $adresseExpediteur,
    ) {
    }

    public function __invoke(AlerteFichesObsoletesMessage $message): void
    {
        $vehicules = $this->publicationVehicule->fichesObsoletes();

        if ([] === $vehicules) {
            return;
        }

        $parametres = $this->parametresSiteRepository->getInstance();

        $email = (new TemplatedEmail())
            ->from(new Address($this->adresseExpediteur, 'Horizon Auto — site web'))
            ->to($parametres->getEmail() ?: $this->adresseExpediteur)
            ->subject('Fiches véhicules potentiellement obsolètes')
            ->htmlTemplate('emails/fiches_obsoletes.html.twig')
            ->context(['vehicules' => $vehicules]);

        $this->mailer->send($email);
    }
}
