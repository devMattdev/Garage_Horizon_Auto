<?php

declare(strict_types=1);

namespace App\Service\Vehicule;

use App\Entity\Vehicule;
use App\Enum\StatutVehicule;
use App\Event\VehiculePublieEvent;
use App\Event\VehiculeVenduEvent;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Applique les règles RG-04 à RG-06 lors des changements de statut d'un véhicule
 * (back.md section 6.3 et 9).
 */
class PublicationVehicule
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VehiculeRepository $vehiculeRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * RG-04 : un véhicule ne peut passer au statut PUBLIE que s'il possède
     * au moins une photo, un prix et un kilométrage.
     */
    public function publier(Vehicule $vehicule): void
    {
        if ($vehicule->getPhotos()->isEmpty()) {
            throw new ValidatorException('Un véhicule doit avoir au moins une photo pour être publié.');
        }

        if (null === $vehicule->getPrix()) {
            throw new ValidatorException('Un véhicule doit avoir un prix pour être publié.');
        }

        if ($vehicule->getKilometrage() <= 0) {
            throw new ValidatorException('Un véhicule doit avoir un kilométrage pour être publié.');
        }

        $vehicule->setStatut(StatutVehicule::PUBLIE);
        $vehicule->setPublieLe(new \DateTimeImmutable());
        $vehicule->setModifieLe(new \DateTimeImmutable());

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new VehiculePublieEvent($vehicule));
    }

    public function marquerVendu(Vehicule $vehicule): void
    {
        $vehicule->setStatut(StatutVehicule::VENDU);
        $vehicule->setModifieLe(new \DateTimeImmutable());

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new VehiculeVenduEvent($vehicule));
    }

    /**
     * RG-05 : un véhicule VENDU reste consultable 30 jours puis passe en RETIRE.
     */
    public function retirerVehiculesVendusExpires(): int
    {
        $seuil = (new \DateTimeImmutable())->modify('-30 days');
        $vehicules = $this->vehiculeRepository->findVendusDepuisPlusDe($seuil);

        foreach ($vehicules as $vehicule) {
            $vehicule->setStatut(StatutVehicule::RETIRE);
            $vehicule->setModifieLe(new \DateTimeImmutable());
        }

        $this->entityManager->flush();

        return \count($vehicules);
    }

    /**
     * RG-06 : un véhicule publié sans modification depuis 60 jours déclenche
     * une alerte interne (fiche potentiellement obsolète).
     *
     * @return Vehicule[]
     */
    public function fichesObsoletes(): array
    {
        return $this->vehiculeRepository->findPubliesSansModificationDepuis(
            (new \DateTimeImmutable())->modify('-60 days'),
        );
    }
}
