<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Vehicule;

use App\Entity\PhotoVehicule;
use App\Entity\Vehicule;
use App\Enum\BoiteVitesses;
use App\Enum\Energie;
use App\Enum\StatutVehicule;
use App\Event\VehiculePublieEvent;
use App\Event\VehiculeVenduEvent;
use App\Repository\VehiculeRepository;
use App\Service\Vehicule\PublicationVehicule;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

final class PublicationVehiculeTest extends TestCase
{
    private function creerVehicule(): Vehicule
    {
        $vehicule = new Vehicule();
        $vehicule->setReference('VH-001');
        $vehicule->setTitre('Peugeot 208');
        $vehicule->setMarque('Peugeot');
        $vehicule->setModele('208');
        $vehicule->setMisEnCirculationLe(new \DateTimeImmutable('2020-01-01'));
        $vehicule->setKilometrage(50000);
        $vehicule->setEnergie(Energie::ESSENCE);
        $vehicule->setBoite(BoiteVitesses::MANUELLE);
        $vehicule->setDescription('Description.');

        return $vehicule;
    }

    /**
     * RG-04 : impossible de publier sans photo.
     */
    public function testPublierEchoueSansPhoto(): void
    {
        $vehicule = $this->creerVehicule();
        $vehicule->setPrix('12000.00');

        $publicationVehicule = new PublicationVehicule(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(VehiculeRepository::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $this->expectException(ValidatorException::class);
        $publicationVehicule->publier($vehicule);
    }

    /**
     * RG-04 : impossible de publier sans prix.
     */
    public function testPublierEchoueSansPrix(): void
    {
        $vehicule = $this->creerVehicule();
        $vehicule->addPhoto((new PhotoVehicule())->setFichier('photo.jpg')->setTexteAlternatif('Peugeot 208'));

        $publicationVehicule = new PublicationVehicule(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(VehiculeRepository::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $this->expectException(ValidatorException::class);
        $publicationVehicule->publier($vehicule);
    }

    /**
     * RG-04 : impossible de publier sans kilométrage.
     */
    public function testPublierEchoueSansKilometrage(): void
    {
        $vehicule = $this->creerVehicule();
        $vehicule->setKilometrage(0);
        $vehicule->addPhoto((new PhotoVehicule())->setFichier('photo.jpg')->setTexteAlternatif('Peugeot 208'));
        $vehicule->setPrix('12000.00');

        $publicationVehicule = new PublicationVehicule(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(VehiculeRepository::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $this->expectException(ValidatorException::class);
        $publicationVehicule->publier($vehicule);
    }

    public function testPublierReussitAvecPhotoEtPrixEtDeclencheEvenement(): void
    {
        $vehicule = $this->creerVehicule();
        $vehicule->addPhoto((new PhotoVehicule())->setFichier('photo.jpg')->setTexteAlternatif('Peugeot 208'));
        $vehicule->setPrix('12000.00');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(VehiculePublieEvent::class));

        $publicationVehicule = new PublicationVehicule(
            $entityManager,
            $this->createMock(VehiculeRepository::class),
            $eventDispatcher,
        );

        $publicationVehicule->publier($vehicule);

        self::assertSame(StatutVehicule::PUBLIE, $vehicule->getStatut());
        self::assertNotNull($vehicule->getPublieLe());
    }

    public function testMarquerVenduChangeLeStatutEtDeclencheEvenement(): void
    {
        $vehicule = $this->creerVehicule();
        $vehicule->setStatut(StatutVehicule::PUBLIE);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(VehiculeVenduEvent::class));

        $publicationVehicule = new PublicationVehicule(
            $entityManager,
            $this->createMock(VehiculeRepository::class),
            $eventDispatcher,
        );

        $publicationVehicule->marquerVendu($vehicule);

        self::assertSame(StatutVehicule::VENDU, $vehicule->getStatut());
    }

    /**
     * RG-05 : un véhicule VENDU depuis plus de 30 jours passe automatiquement en RETIRE.
     */
    public function testRetirerVehiculesVendusExpiresChangeLeStatut(): void
    {
        $vehicule = $this->creerVehicule();
        $vehicule->setStatut(StatutVehicule::VENDU);

        $vehiculeRepository = $this->createMock(VehiculeRepository::class);
        $vehiculeRepository->method('findVendusDepuisPlusDe')->willReturn([$vehicule]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $publicationVehicule = new PublicationVehicule(
            $entityManager,
            $vehiculeRepository,
            $this->createMock(EventDispatcherInterface::class),
        );

        $nombreRetires = $publicationVehicule->retirerVehiculesVendusExpires();

        self::assertSame(1, $nombreRetires);
        self::assertSame(StatutVehicule::RETIRE, $vehicule->getStatut());
    }
}
