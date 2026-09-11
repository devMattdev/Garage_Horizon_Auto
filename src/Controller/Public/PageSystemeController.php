<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\ParametresSiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages système (mentions légales, confidentialité) — back.md section 24, point 6 :
 * le contenu juridique définitif reste à valider (CNIL) avant mise en ligne.
 */
class PageSystemeController extends AbstractController
{
    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentionsLegales(ParametresSiteRepository $parametresSiteRepository): Response
    {
        return $this->render('public/page_systeme/mentions_legales.html.twig', [
            'parametres' => $parametresSiteRepository->getInstance(),
        ]);
    }

    #[Route('/politique-de-confidentialite', name: 'politique_confidentialite')]
    public function politiqueConfidentialite(ParametresSiteRepository $parametresSiteRepository): Response
    {
        return $this->render('public/page_systeme/politique_confidentialite.html.twig', [
            'parametres' => $parametresSiteRepository->getInstance(),
        ]);
    }

    #[Route('/accessibilite', name: 'declaration_accessibilite')]
    public function declarationAccessibilite(): Response
    {
        return $this->render('public/page_systeme/accessibilite.html.twig');
    }
}
