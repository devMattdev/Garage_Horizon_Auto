<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\MembreEquipeRepository;
use App\Repository\PrestationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GarageController extends AbstractController
{
    #[Route('/le-garage', name: 'garage')]
    public function __invoke(PrestationRepository $prestationRepository, MembreEquipeRepository $membreEquipeRepository): Response
    {
        return $this->render('public/garage/index.html.twig', [
            'prestations' => $prestationRepository->findActivesOrdonnees(),
            'membres_equipe' => $membreEquipeRepository->findActifsOrdonnes(),
        ]);
    }
}
