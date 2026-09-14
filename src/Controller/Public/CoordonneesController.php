<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\FermetureExceptionnelleRepository;
use App\Repository\PlageHoraireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CoordonneesController extends AbstractController
{
    #[Route('/coordonnees', name: 'coordonnees')]
    public function __invoke(
        PlageHoraireRepository $plageHoraireRepository,
        FermetureExceptionnelleRepository $fermetureExceptionnelleRepository,
    ): Response {
        return $this->render('public/contact/index.html.twig', [
            'horaires' => $plageHoraireRepository->findToutesIndexeesParJour(),
            'fermetures' => $fermetureExceptionnelleRepository->trouverAvenir(new \DateTimeImmutable('today')),
        ]);
    }
}
