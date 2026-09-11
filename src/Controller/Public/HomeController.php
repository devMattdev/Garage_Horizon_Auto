<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\PrestationRepository;
use App\Repository\VehiculeRepository;
use App\Service\Horaires\CalculateurOuverture;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function __invoke(
        PrestationRepository $prestationRepository,
        VehiculeRepository $vehiculeRepository,
        CalculateurOuverture $calculateurOuverture,
    ): Response {
        $prestationsEnAvant = array_values(array_filter(
            $prestationRepository->findActivesOrdonnees(),
            static fn ($p) => $p->isMiseEnAvant(),
        ));

        return $this->render('public/home/index.html.twig', [
            'prestations' => [] !== $prestationsEnAvant ? $prestationsEnAvant : $prestationRepository->findActivesOrdonnees(),
            'vehicules' => \array_slice($vehiculeRepository->findPublies(), 0, 6),
            'ouvert_maintenant' => $calculateurOuverture->estOuvert(new \DateTimeImmutable()),
        ]);
    }
}
