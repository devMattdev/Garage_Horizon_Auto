<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\QuestionFrequenteRepository;
use App\Service\Seo\GenerateurDonneesStructurees;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FaqController extends AbstractController
{
    #[Route('/questions-frequentes', name: 'faq')]
    public function __invoke(
        Request $request,
        QuestionFrequenteRepository $questionFrequenteRepository,
        GenerateurDonneesStructurees $generateurDonneesStructurees,
    ): Response {
        $toutesLesQuestions = $questionFrequenteRepository->findActivesOrdonnees();
        $recherche = trim($request->query->getString('q'));
        $questions = '' === $recherche ? $toutesLesQuestions : array_values(array_filter(
            $toutesLesQuestions,
            static fn ($question): bool => str_contains(mb_strtolower($question->getQuestion().' '.$question->getReponse()), mb_strtolower($recherche)),
        ));

        return $this->render('public/faq/index.html.twig', [
            'questions' => $questions,
            'donnees_structurees' => $generateurDonneesStructurees->faqPage($toutesLesQuestions),
            'recherche' => $recherche,
        ]);
    }
}
