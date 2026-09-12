<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HealthControllerTest extends WebTestCase
{
    public function testLaSondeValideLApplicationEtLaBaseDeDonnees(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseHeaderSame('x-robots-tag', 'noindex, nofollow');
        self::assertStringContainsString(
            'no-store',
            (string) $client->getResponse()->headers->get('cache-control'),
        );
        self::assertJsonStringEqualsJsonString(
            '{"status":"ok"}',
            (string) $client->getResponse()->getContent(),
        );
    }
}
