<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ParametresSite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParametresSite>
 */
class ParametresSiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParametresSite::class);
    }

    public function getInstance(): ParametresSite
    {
        $parametres = $this->find(1);

        if (null === $parametres) {
            $parametres = new ParametresSite();
            $em = $this->getEntityManager();
            $em->persist($parametres);
            $em->flush();
        }

        return $parametres;
    }
}
