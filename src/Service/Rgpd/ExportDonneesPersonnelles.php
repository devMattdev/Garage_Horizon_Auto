<?php

declare(strict_types=1);

namespace App\Service\Rgpd;

use App\Repository\DemandeRepository;

/**
 * Produit l'export des données liées à une adresse courriel ou un numéro de téléphone
 * (exercice du droit d'accès, back.md section 9).
 */
class ExportDonneesPersonnelles
{
    public function __construct(private readonly DemandeRepository $demandeRepository)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function exporter(?string $email, ?string $telephone): array
    {
        $demandes = $this->demandeRepository->findParEmailOuTelephone($email, $telephone);

        return array_map(static fn ($demande) => [
            'id' => $demande->getId(),
            'type' => $demande->getType()->value,
            'nom' => $demande->getNom(),
            'telephone' => $demande->getTelephone(),
            'email' => $demande->getEmail(),
            'immatriculation' => $demande->getImmatriculation(),
            'kilometrage' => $demande->getKilometrage(),
            'creneauSouhaite' => $demande->getCreneauSouhaite(),
            'message' => $demande->getMessage(),
            'consentementRelance' => $demande->isConsentementRelance(),
            'statut' => $demande->getStatut()->value,
            'creeLe' => $demande->getCreeLe()->format(\DATE_ATOM),
            'rdvConfirmeLe' => $demande->getRdvConfirmeLe()?->format(\DATE_ATOM),
            'purgePrevueLe' => $demande->getPurgePrevueLe()->format('Y-m-d'),
        ], $demandes);
    }
}
