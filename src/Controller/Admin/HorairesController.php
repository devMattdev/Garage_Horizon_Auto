<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PlageHoraire;
use App\Enum\JourSemaine;
use App\Form\Admin\HorairesType;
use App\Repository\PlageHoraireRepository;
use App\Security\Voter\GerantVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HorairesController extends AbstractController
{
    #[Route('/admin/horaires', name: 'admin_horaires')]
    public function __invoke(
        Request $request,
        PlageHoraireRepository $plageHoraireRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted(GerantVoter::RESERVE_GERANT);

        $plagesExistantes = $plageHoraireRepository->findToutesIndexeesParJour();

        $plages = [];
        foreach (JourSemaine::cases() as $jour) {
            if (isset($plagesExistantes[$jour->value])) {
                $plages[$jour->value] = $plagesExistantes[$jour->value];
                continue;
            }

            $plage = new PlageHoraire();
            $plage->setJour($jour);
            $entityManager->persist($plage);
            $plages[$jour->value] = $plage;
        }

        ksort($plages);

        $form = $this->createForm(HorairesType::class, ['plages' => $plages]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Horaires mis à jour.');

            return $this->redirectToRoute('admin_horaires');
        }

        return $this->render('admin/horaires.html.twig', [
            'form' => $form,
            'plages' => $plages,
        ]);
    }
}
