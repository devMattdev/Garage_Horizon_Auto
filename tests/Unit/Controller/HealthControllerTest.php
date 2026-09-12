<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\Public\HealthController;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class HealthControllerTest extends TestCase
{
    public function testRetourneUneIndisponibiliteSansExposerLErreurDeConnexion(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('executeQuery')->willThrowException(new \RuntimeException('secret technique'));

        $reponse = (new HealthController($connection))();

        self::assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $reponse->getStatusCode());
        self::assertJsonStringEqualsJsonString('{"status":"degraded"}', (string) $reponse->getContent());
        self::assertStringNotContainsString('secret technique', (string) $reponse->getContent());
        self::assertSame('noindex, nofollow', $reponse->headers->get('x-robots-tag'));
    }
}
