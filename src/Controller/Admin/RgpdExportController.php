<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\Rgpd\ExportDonneesPersonnelles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RgpdExportController extends AbstractController
{
    #[Route('/admin/rgpd/export', name: 'admin_rgpd_export')]
    public function __invoke(Request $request, ExportDonneesPersonnelles $exportDonneesPersonnelles): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $email = $request->query->get('email');
        $telephone = $request->query->get('telephone');

        $donnees = null;
        if ($email || $telephone) {
            $donnees = $exportDonneesPersonnelles->exporter(
                \is_string($email) && '' !== $email ? $email : null,
                \is_string($telephone) && '' !== $telephone ? $telephone : null,
            );

            if ('json' === $request->query->get('format')) {
                return new JsonResponse($donnees);
            }
        }

        return $this->render('admin/rgpd_export.html.twig', [
            'email' => $email,
            'telephone' => $telephone,
            'donnees' => $donnees,
        ]);
    }
}
