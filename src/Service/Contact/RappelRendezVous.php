<?php

declare(strict_types=1);

namespace App\Service\Contact;

use App\Entity\Demande;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Sélectionne les rendez-vous à J-1 et envoie les rappels (RG-08, back.md section 9 et 11).
 */
class RappelRendezVous
{
    public function __construct(
        private readonly DemandeRepository $demandeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly string $adresseExpediteur,
    ) {
    }

    public function envoyerRappels(?\DateTimeImmutable $maintenant = null): int
    {
        $maintenant ??= new \DateTimeImmutable();
        $debutFenetre = $maintenant->modify('+23 hours');
        $finFenetre = $maintenant->modify('+25 hours');

        $demandes = $this->demandeRepository->findRappelsAEnvoyer($debutFenetre, $finFenetre);

        foreach ($demandes as $demande) {
            $this->envoyerRappel($demande);
            $demande->setRappelEnvoyeLe($maintenant);
        }

        $this->entityManager->flush();

        return \count($demandes);
    }

    private function envoyerRappel(Demande $demande): void
    {
        $destinataire = $demande->getEmail();
        if (null === $destinataire) {
            // findRappelsAEnvoyer() filtre déjà sur email IS NOT NULL ; garde défensive.
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->adresseExpediteur, 'Horizon Auto'))
            ->to($destinataire)
            ->subject('Rappel de votre rendez-vous — Horizon Auto')
            ->htmlTemplate('emails/rappel_rendez_vous.html.twig')
            ->context(['demande' => $demande]);

        $this->mailer->send($email);
    }
}
