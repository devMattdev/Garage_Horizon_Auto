<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Entity\Prestation;
use App\Enum\FamillePrestation;
use App\Repository\PrestationRepository;
use App\Service\Seo\GenerateurDonneesStructurees;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrestationController extends AbstractController
{
    #[Route('/prestations', name: 'prestations_index')]
    public function index(Request $request, PrestationRepository $prestationRepository): Response
    {
        $famille = FamillePrestation::tryFrom($request->query->getString('famille'));
        $prestations = $prestationRepository->findActivesOrdonnees($famille);

        // La rubrique Entretien possède une véritable page d'atterrissage détaillée.
        // Le menu historique conserve son URL filtrée, puis mène à la prestation publiée.
        if (FamillePrestation::ENTRETIEN === $famille && isset($prestations[0])) {
            return $this->redirectToRoute('prestation_detail', [
                'slug' => $prestations[0]->getSlug(),
            ]);
        }

        return $this->render('public/prestation/index.html.twig', [
            'prestations' => $prestations,
            'famille' => $famille,
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
