<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\PageLocaleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageLocaleController extends AbstractController
{
    #[Route('/garage-automobile/{commune}', name: 'page_locale_detail')]
    public function __invoke(string $commune, PageLocaleRepository $pageLocaleRepository): Response
    {
        $page = $pageLocaleRepository->findActiveParSlug($commune);

        if (null === $page) {
            throw $this->createNotFoundException();
        }

        return $this->render('public/page_locale/detail.html.twig', ['page' => $page]);
    }
}
