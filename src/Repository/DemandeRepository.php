<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Demande;
use App\Enum\TypeDemande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Demande>
 */
class DemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demande::class);
    }

    /**
     * RG-12 : deux soumissions identiques (même courriel, même type, même message)
     * à moins de 5 minutes d'intervalle sont considérées comme un doublon.
     */
    public function trouverDoublonRecent(string $email, TypeDemande $type, ?string $message, \DateTimeImmutable $depuis): ?Demande
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.email = :email')
            ->andWhere('d.type = :type')
            ->andWhere('d.message = :message')
            ->andWhere('d.creeLe >= :depuis')
            ->setParameter('email', $email)
            ->setParameter('type', $type)
            ->setParameter('message', $message)
            ->setParameter('depuis', $depuis)
            ->orderBy('d.creeLe', \SortDirection::Descending)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Demande[]
     */
    public function findAPurger(\DateTimeImmutable $aujourdhui): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.purgePrevueLe <= :aujourdhui')
            ->setParameter('aujourdhui', $aujourdhui)
            ->getQuery()
            ->getResult();
    }

    /**
     * RG-08 : rendez-vous confirmés à J-1, non encore rappelés, avec un courriel.
     *
     * @return Demande[]
     */
    public function findRappelsAEnvoyer(\DateTimeImmutable $debutFenetre, \DateTimeImmutable $finFenetre): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.rdvConfirmeLe BETWEEN :debut AND :fin')
            ->andWhere('d.email IS NOT NULL')
            ->andWhere('d.rappelEnvoyeLe IS NULL')
            ->setParameter('debut', $debutFenetre)
            ->setParameter('fin', $finFenetre)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Demande[]
     */
    public function findParEmailOuTelephone(?string $email, ?string $telephone): array
    {
        $qb = $this->createQueryBuilder('d');

        $qb->andWhere($qb->expr()->orX(
            null !== $email ? 'd.email = :email' : '1 = 0',
            null !== $telephone ? 'd.telephone = :telephone' : '1 = 0',
        ));

        if (null !== $email) {
            $qb->setParameter('email', $email);
        }

        if (null !== $telephone) {
            $qb->setParameter('telephone', $telephone);
        }

        return $qb->getQuery()->getResult();
    }
}
