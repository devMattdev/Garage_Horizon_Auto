<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Vehicule;
use App\Enum\BoiteVitesses;
use App\Enum\Energie;
use App\Enum\StatutVehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicule>
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    /**
     * @return Vehicule[]
     */
    public function findPublies(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.statut = :statut')
            ->setParameter('statut', StatutVehicule::PUBLIE)
            ->orderBy('v.publieLe', \SortDirection::Descending)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Vehicule[]
     */
    public function findPubliesFiltres(?Energie $energie, ?BoiteVitesses $boite, string $tri): array
    {
        $queryBuilder = $this->createQueryBuilder('v')
            ->andWhere('v.statut = :statut')
            ->setParameter('statut', StatutVehicule::PUBLIE);

        if (null !== $energie) {
            $queryBuilder->andWhere('v.energie = :energie')->setParameter('energie', $energie);
        }
        if (null !== $boite) {
            $queryBuilder->andWhere('v.boite = :boite')->setParameter('boite', $boite);
        }

        [$champ, $direction] = match ($tri) {
            'prix-asc' => ['v.prix', 'ASC'],
            'prix-desc' => ['v.prix', 'DESC'],
            'kilometrage' => ['v.kilometrage', 'ASC'],
            default => ['v.publieLe', 'DESC'],
        };

        return $queryBuilder->orderBy($champ, $direction)->getQuery()->getResult();
    }

    /**
     * @return Vehicule[]
     */
    public function findVendusDepuisPlusDe(\DateTimeImmutable $seuil): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.statut = :statut')
            ->andWhere('v.modifieLe < :seuil')
            ->setParameter('statut', StatutVehicule::VENDU)
            ->setParameter('seuil', $seuil)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Vehicule[]
     */
    public function findPubliesSansModificationDepuis(\DateTimeImmutable $seuil): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.statut = :statut')
            ->andWhere('v.modifieLe < :seuil')
            ->setParameter('statut', StatutVehicule::PUBLIE)
            ->setParameter('seuil', $seuil)
            ->getQuery()
            ->getResult();
    }
}
