<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Entity\Prestation;
use App\Repository\PrestationRepository;
use App\Service\Seo\GenerateurDonneesStructurees;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrestationController extends AbstractController
{
    #[Route('/prestations', name: 'prestations_index')]
    public function index(PrestationRepository $prestationRepository): Response
    {
        return $this->render('public/prestation/index.html.twig', [
            'prestations' => $prestationRepository->findActivesOrdonnees(),
        ]);
    }

    #[Route('/prestations/{slug}', name: 'prestation_detail')]
    public function detail(Prestation $prestation, GenerateurDonneesStructurees $generateurDonneesStructurees): Response
    {
        if (!$prestation->isActif()) {
            throw $this->createNotFoundException();
        }

        return $this->render('public/prestation/detail.html.twig', [
            'prestation' => $prestation,
            'donnees_structurees' => $generateurDonneesStructurees->service($prestation),
        ]);
    }
}
