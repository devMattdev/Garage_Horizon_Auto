<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PlageHoraire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlageHoraire>
 */
class PlageHoraireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlageHoraire::class);
    }

    /**
     * @return PlageHoraire[] indexé par le numéro ISO du jour (1 = lundi)
     */
    public function findToutesIndexeesParJour(): array
    {
        $plages = [];
        foreach ($this->findAll() as $plage) {
            $plages[$plage->getJour()->value] = $plage;
        }

        return $plages;
    }
}
