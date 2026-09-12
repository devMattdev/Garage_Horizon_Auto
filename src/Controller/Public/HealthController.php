<?php

declare(strict_types=1);

namespace App\Controller\Public;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Sonde volontairement minimale : elle valide PHP et la connexion MySQL sans
 * exposer de version, de trace ou de detail d'infrastructure.
 */
final readonly class HealthController
{
    public function __construct(private Connection $connection)
    {
    }

    #[Route('/health', name: 'health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        try {
            $this->connection->executeQuery('SELECT 1');
            $reponse = new JsonResponse(['status' => 'ok']);
        } catch (\Throwable) {
            $reponse = new JsonResponse(['status' => 'degraded'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $reponse->headers->set('Cache-Control', 'no-store');
        $reponse->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $reponse;
    }
}
