<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Bandeau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bandeau>
 */
class BandeauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bandeau::class);
    }

    public function trouverActifCourant(\DateTimeImmutable $maintenant): ?Bandeau
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.actif = true')
            ->andWhere('b.debutLe IS NULL OR b.debutLe <= :maintenant')
            ->andWhere('b.finLe IS NULL OR b.finLe >= :maintenant')
            ->setParameter('maintenant', $maintenant)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
