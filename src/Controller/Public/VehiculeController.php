<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Entity\Vehicule;
use App\Enum\StatutVehicule;
use App\Repository\VehiculeRepository;
use App\Service\Seo\GenerateurDonneesStructurees;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VehiculeController extends AbstractController
{
    #[Route('/vehicules-occasion', name: 'vehicules_index')]
    public function index(VehiculeRepository $vehiculeRepository): Response
    {
        return $this->render('public/vehicule/index.html.twig', [
            'vehicules' => $vehiculeRepository->findPublies(),
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
