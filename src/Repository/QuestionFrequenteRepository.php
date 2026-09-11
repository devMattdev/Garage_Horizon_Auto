<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\QuestionFrequente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuestionFrequente>
 */
class QuestionFrequenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionFrequente::class);
    }

    /**
     * @return QuestionFrequente[]
     */
    public function findActivesOrdonnees(): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.actif = true')
            ->orderBy('q.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
