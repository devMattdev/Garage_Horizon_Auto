<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class DatabaseWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \LogicException('Le gestionnaire d’entités Doctrine est indisponible.');
        }
        $this->entityManager = $entityManager;

        $metadonnees = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $outilSchema = new SchemaTool($this->entityManager);
        $outilSchema->dropSchema($metadonnees);
        $outilSchema->createSchema($metadonnees);
    }
}
