<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Entity\PhotoVehicule;
use App\Enum\StatutVehicule;
use App\Service\Vehicule\StockagePhotoVehicule;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PhotoVehiculeController extends AbstractController
{
    #[Route('/media/vehicules/{id}/{variante}.{format}', name: 'photo_vehicule', requirements: ['id' => '\\d+', 'variante' => 'vignette|carte|detail', 'format' => 'webp|jpg'], methods: ['GET'])]
    public function __invoke(PhotoVehicule $photo, string $variante, string $format, StockagePhotoVehicule $stockage): Response
    {
        if (!\in_array($photo->getVehicule()->getStatut(), [StatutVehicule::PUBLIE, StatutVehicule::VENDU], true)) {
            throw $this->createNotFoundException();
        }

        $chemin = $stockage->cheminVariante($photo, $variante, $format);
        if (!is_file($chemin)) {
            throw $this->createNotFoundException('Cette variante est en cours de génération.');
        }

        $response = new BinaryFileResponse($chemin);
        $response->headers->set('Content-Type', 'webp' === $format ? 'image/webp' : 'image/jpeg');
        $response->setPublic();
        $response->setMaxAge(31_536_000);
        $response->setImmutable();

        return $response;
    }
}
