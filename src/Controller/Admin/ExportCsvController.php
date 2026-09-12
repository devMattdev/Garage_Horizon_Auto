<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Demande;
use App\Entity\Vehicule;
use App\Repository\DemandeRepository;
use App\Repository\VehiculeRepository;
use App\Service\Export\ExportCsv;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Exports CSV des Demandes et des Véhicules depuis le back-office (back.md section 15).
 */
class ExportCsvController extends AbstractController
{
    public function __construct(private readonly ExportCsv $exportCsv)
    {
    }

    #[Route('/admin/demandes/export.csv', name: 'admin_demande_export_csv')]
    public function demandes(DemandeRepository $demandeRepository): StreamedResponse
    {
        $entetes = [
            'Type', 'Nom', 'Téléphone', 'Courriel', 'Immatriculation', 'Kilométrage',
            'Créneau souhaité', 'Statut', 'Créée le', 'RDV confirmé le',
        ];

        return $this->exportCsv->creerReponse('demandes.csv', $entetes, $this->lignesDemandes($demandeRepository));
    }

    /**
     * @return iterable<list<string>>
     */
    private function lignesDemandes(DemandeRepository $demandeRepository): iterable
    {
        foreach ($demandeRepository->findAll() as $demande) {
            yield $this->ligneDemande($demande);
        }
    }

    /**
     * @return list<string>
     */
    private function ligneDemande(Demande $demande): array
    {
        return [
            $demande->getType()->value,
            $demande->getNom(),
            $demande->getTelephone() ?? '',
            $demande->getEmail() ?? '',
            $demande->getImmatriculation() ?? '',
            null !== $demande->getKilometrage() ? (string) $demande->getKilometrage() : '',
            $demande->getCreneauSouhaite() ?? '',
            $demande->getStatut()->value,
            $demande->getCreeLe()->format('Y-m-d H:i'),
            $demande->getRdvConfirmeLe()?->format('Y-m-d H:i') ?? '',
        ];
    }

    #[Route('/admin/vehicules/export.csv', name: 'admin_vehicule_export_csv')]
    public function vehicules(VehiculeRepository $vehiculeRepository): StreamedResponse
    {
        $entetes = [
            'Référence', 'Titre', 'Marque', 'Modèle', 'Mise en circulation', 'Kilométrage',
            'Énergie', 'Boîte', 'Prix', 'Statut', 'Publié le',
        ];

        return $this->exportCsv->creerReponse('vehicules.csv', $entetes, $this->lignesVehicules($vehiculeRepository));
    }

    /**
     * @return iterable<list<string>>
     */
    private function lignesVehicules(VehiculeRepository $vehiculeRepository): iterable
    {
        foreach ($vehiculeRepository->findAll() as $vehicule) {
            yield $this->ligneVehicule($vehicule);
        }
    }

    /**
     * @return list<string>
     */
    private function ligneVehicule(Vehicule $vehicule): array
    {
        return [
            $vehicule->getReference(),
            $vehicule->getTitre(),
            $vehicule->getMarque(),
            $vehicule->getModele(),
            $vehicule->getMisEnCirculationLe()->format('Y-m-d'),
            (string) $vehicule->getKilometrage(),
            $vehicule->getEnergie()->value,
            $vehicule->getBoite()->value,
            $vehicule->getPrix() ?? '',
            $vehicule->getStatut()->value,
            $vehicule->getPublieLe()?->format('Y-m-d H:i') ?? '',
        ];
    }
}
