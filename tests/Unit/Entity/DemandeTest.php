<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Demande;
use App\Entity\Vehicule;
use App\Enum\BoiteVitesses;
use App\Enum\Energie;
use App\Enum\StatutVehicule;
use App\Enum\TypeDemande;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * RG-03 (back.md section 6.3) : une demande de type VEHICULE doit référencer
 * un véhicule au statut PUBLIE.
 */
final class DemandeTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function creerVehicule(StatutVehicule $statut): Vehicule
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
        $vehicule->setStatut($statut);

        return $vehicule;
    }

    public function testDemandeVehiculeSansVehiculeEstInvalide(): void
    {
        $demande = new Demande();
        $demande->setType(TypeDemande::VEHICULE);

        $violations = $this->validator->validate($demande);

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('vehicule', $violations->get(0)->getPropertyPath());
    }

    public function testDemandeVehiculeAvecVehiculeNonPublieEstInvalide(): void
    {
        $demande = new Demande();
        $demande->setType(TypeDemande::VEHICULE);
        $demande->setVehicule($this->creerVehicule(StatutVehicule::BROUILLON));

        $violations = $this->validator->validate($demande);

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('vehicule', $violations->get(0)->getPropertyPath());
    }

    public function testDemandeVehiculeAvecVehiculePublieEstValide(): void
    {
        $demande = new Demande();
        $demande->setType(TypeDemande::VEHICULE);
        $demande->setVehicule($this->creerVehicule(StatutVehicule::PUBLIE));

        $violations = $this->validator->validate($demande);

        self::assertCount(0, $violations);
    }

    public function testDemandeHorsVehiculeNExigeAucunVehicule(): void
    {
        $demande = new Demande();
        $demande->setType(TypeDemande::QUESTION);

        $violations = $this->validator->validate($demande);

        self::assertCount(0, $violations);
    }
}
