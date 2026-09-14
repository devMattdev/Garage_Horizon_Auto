<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Prestation;
use App\Enum\FamillePrestation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prestation>
 */
class PrestationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prestation::class);
    }

    /**
     * @return Prestation[]
     */
    public function findActivesOrdonnees(?FamillePrestation $famille = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.actif = true')
            ->orderBy('p.ordre', \SortDirection::Ascending)
            ->addOrderBy('p.nom', \SortDirection::Ascending);

        if (null !== $famille) {
            $qb->andWhere('p.famille = :famille')->setParameter('famille', $famille);
        }

        return $qb->getQuery()->getResult();
    }
}
