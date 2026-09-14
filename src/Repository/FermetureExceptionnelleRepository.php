<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FermetureExceptionnelle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FermetureExceptionnelle>
 */
class FermetureExceptionnelleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FermetureExceptionnelle::class);
    }

    public function trouverCouvrant(\DateTimeImmutable $instant): ?FermetureExceptionnelle
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.debutLe <= :instant')
            ->andWhere('f.finLe >= :instant')
            ->setParameter('instant', $instant)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return FermetureExceptionnelle[]
     */
    public function trouverAvenir(\DateTimeImmutable $depuis, int $limite = 5): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.finLe >= :depuis')
            ->setParameter('depuis', $depuis)
            ->orderBy('f.debutLe', 'ASC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }
}
