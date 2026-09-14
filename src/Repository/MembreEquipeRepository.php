<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MembreEquipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MembreEquipe>
 */
class MembreEquipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembreEquipe::class);
    }

    /**
     * @return MembreEquipe[]
     */
    public function findActifsOrdonnes(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.actif = true')
            ->orderBy('m.ordre', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }
}
