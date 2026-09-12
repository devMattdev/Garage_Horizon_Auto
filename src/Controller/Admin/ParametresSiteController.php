<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\ParametresSiteType;
use App\Repository\ParametresSiteRepository;
use App\Security\Voter\GerantVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ParametresSiteController extends AbstractController
{
    #[Route('/admin/parametres', name: 'admin_parametres')]
    public function __invoke(
        Request $request,
        ParametresSiteRepository $parametresSiteRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted(GerantVoter::RESERVE_GERANT);

        $parametres = $parametresSiteRepository->getInstance();

        $form = $this->createForm(ParametresSiteType::class, $parametres);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Paramètres du site mis à jour.');

            return $this->redirectToRoute('admin_parametres');
        }

        return $this->render('admin/parametres.html.twig', [
            'form' => $form,
        ]);
    }
}
