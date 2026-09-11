<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\QuestionFrequenteRepository;
use App\Service\Seo\GenerateurDonneesStructurees;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FaqController extends AbstractController
{
    #[Route('/faq', name: 'faq')]
    public function __invoke(
        QuestionFrequenteRepository $questionFrequenteRepository,
        GenerateurDonneesStructurees $generateurDonneesStructurees,
    ): Response {
        $questions = $questionFrequenteRepository->findActivesOrdonnees();

        return $this->render('public/faq/index.html.twig', [
            'questions' => $questions,
            'donnees_structurees' => $generateurDonneesStructurees->faqPage($questions),
        ]);
    }
}
