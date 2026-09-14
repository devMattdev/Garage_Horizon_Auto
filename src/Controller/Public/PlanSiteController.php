<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\PageLocaleRepository;
use App\Repository\PrestationRepository;
use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlanSiteController extends AbstractController
{
    #[Route('/plan-du-site', name: 'plan_site')]
    public function __invoke(
        PrestationRepository $prestationRepository,
        VehiculeRepository $vehiculeRepository,
        PageLocaleRepository $pageLocaleRepository,
    ): Response {
        return $this->render('public/page_systeme/plan_site.html.twig', [
            'prestations' => $prestationRepository->findActivesOrdonnees(),
            'vehicules' => $vehiculeRepository->findPublies(),
            'pages_locales' => $pageLocaleRepository->findBy(['actif' => true], ['commune' => 'ASC']),
        ]);
    }
}
