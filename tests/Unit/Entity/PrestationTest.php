<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Prestation;
use App\Enum\FamillePrestation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * RG-07 (back.md section 6.3) : un tarif indicatif affiché ne peut être négatif ni nul.
 */
final class PrestationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function creerPrestation(?string $tarifIndicatif): Prestation
    {
        $prestation = new Prestation();
        $prestation->setSlug('revision');
        $prestation->setNom('Révision');
        $prestation->setFamille(FamillePrestation::ENTRETIEN);
        $prestation->setResume('Résumé.');
        $prestation->setDescription('Description.');
        $prestation->setTarifIndicatif($tarifIndicatif);

        return $prestation;
    }

    public function testTarifIndicatifNulEstValide(): void
    {
        $violations = $this->validator->validate($this->creerPrestation(null));

        self::assertCount(0, $violations);
    }

    public function testTarifIndicatifPositifEstValide(): void
    {
        $violations = $this->validator->validate($this->creerPrestation('89.00'));

        self::assertCount(0, $violations);
    }

    public function testTarifIndicatifZeroEstInvalide(): void
    {
        $violations = $this->validator->validate($this->creerPrestation('0.00'));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('tarifIndicatif', $violations->get(0)->getPropertyPath());
    }

    public function testTarifIndicatifNegatifEstInvalide(): void
    {
        $violations = $this->validator->validate($this->creerPrestation('-10.00'));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('tarifIndicatif', $violations->get(0)->getPropertyPath());
    }
}
