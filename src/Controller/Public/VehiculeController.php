<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Entity\Vehicule;
use App\Enum\BoiteVitesses;
use App\Enum\Energie;
use App\Enum\StatutVehicule;
use App\Repository\VehiculeRepository;
use App\Service\Seo\GenerateurDonneesStructurees;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VehiculeController extends AbstractController
{
    #[Route('/vehicules-occasion', name: 'vehicules_index')]
    public function index(Request $request, VehiculeRepository $vehiculeRepository): Response
    {
        $tousLesVehicules = $vehiculeRepository->findPublies();
        $afficherFiltres = \count($tousLesVehicules) > 6;
        $energie = $afficherFiltres ? Energie::tryFrom($request->query->getString('energie')) : null;
        $boite = $afficherFiltres ? BoiteVitesses::tryFrom($request->query->getString('boite')) : null;
        $tri = $afficherFiltres ? $request->query->getString('tri', 'recent') : 'recent';

        return $this->render('public/vehicule/index.html.twig', [
            'vehicules' => $afficherFiltres
                ? $vehiculeRepository->findPubliesFiltres($energie, $boite, $tri)
                : $tousLesVehicules,
            'afficher_filtres' => $afficherFiltres,
            'energie_active' => $energie?->value,
            'boite_active' => $boite?->value,
            'tri_actif' => $tri,
        ]);
    }

    #[Route('/vehicules-occasion/{reference}', name: 'vehicule_detail')]
    public function detail(Vehicule $vehicule, GenerateurDonneesStructurees $generateurDonneesStructurees): Response
    {
        if (!\in_array($vehicule->getStatut(), [StatutVehicule::PUBLIE, StatutVehicule::VENDU], true)) {
            throw $this->createNotFoundException();
        }

        return $this->render('public/vehicule/detail.html.twig', [
            'vehicule' => $vehicule,
            'donnees_structurees' => $generateurDonneesStructurees->vehicle($vehicule),
        ]);
    }
}
