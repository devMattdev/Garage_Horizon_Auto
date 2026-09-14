<?php

declare(strict_types=1);

namespace App\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfessionnelsController extends AbstractController
{
    #[Route('/professionnels', name: 'professionnels')]
    public function __invoke(): Response
    {
        return $this->render('public/professionnels/index.html.twig');
    }
}
