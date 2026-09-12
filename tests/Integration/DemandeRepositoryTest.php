<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Demande;
use App\Enum\TypeDemande;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DemandeRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DemandeRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = static::getContainer()->get(DemandeRepository::class);
        if (!$entityManager instanceof EntityManagerInterface || !$repository instanceof DemandeRepository) {
            throw new \LogicException('Les services Doctrine de test sont indisponibles.');
        }

        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $metadonnees = $entityManager->getMetadataFactory()->getAllMetadata();
        $outilSchema = new SchemaTool($entityManager);
        $outilSchema->dropSchema($metadonnees);
        $outilSchema->createSchema($metadonnees);
    }

    public function testRetrouveUnDoublonRecentEtIgnoreUnMessageDifferent(): void
    {
        $demande = (new Demande())
            ->setType(TypeDemande::DEVIS)
            ->setNom('Jean Dupont')
            ->setEmail('jean@example.test')
            ->setMessage('Même intervention')
            ->setPurgePrevueLe(new \DateTimeImmutable('+12 months'));
        $this->entityManager->persist($demande);
        $this->entityManager->flush();

        $doublon = $this->repository->trouverDoublonRecent(
            'jean@example.test',
            TypeDemande::DEVIS,
            'Même intervention',
            new \DateTimeImmutable('-5 minutes'),
        );
        $messageDifferent = $this->repository->trouverDoublonRecent(
            'jean@example.test',
            TypeDemande::DEVIS,
            'Autre intervention',
            new \DateTimeImmutable('-5 minutes'),
        );

        self::assertSame($demande->getId(), $doublon?->getId());
        self::assertNull($messageDifferent);
    }
}
