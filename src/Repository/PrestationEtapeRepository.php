<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PrestationEtape;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrestationEtape>
 */
class PrestationEtapeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrestationEtape::class);
    }
}
