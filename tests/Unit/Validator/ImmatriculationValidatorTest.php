<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Validator\Constraints\Immatriculation;
use App\Validator\Constraints\ImmatriculationValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * RG-02 (back.md section 6.3) : l'immatriculation, si renseignée, doit correspondre
 * au format SIV ou à l'ancien format FNI.
 *
 * @extends ConstraintValidatorTestCase<ImmatriculationValidator>
 */
final class ImmatriculationValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ImmatriculationValidator
    {
        return new ImmatriculationValidator();
    }

    public function testNullEstValide(): void
    {
        $this->validator->validate(null, new Immatriculation());

        $this->assertNoViolation();
    }

    #[DataProvider('provideFormatsValides')]
    public function testFormatsValides(string $valeur): void
    {
        $this->validator->validate($valeur, new Immatriculation());

        $this->assertNoViolation();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideFormatsValides(): iterable
    {
        yield 'SIV normalisé' => ['AB123CD'];
        yield 'SIV avec tirets' => ['AB-123-CD'];
        yield 'SIV avec espaces et minuscules' => ['ab 123 cd'];
        yield 'FNI normalisé' => ['1234AB75'];
        yield 'FNI avec espaces' => ['1234 AB 75'];
    }

    #[DataProvider('provideFormatsInvalides')]
    public function testFormatsInvalides(string $valeur): void
    {
        $constraint = new Immatriculation();

        $this->validator->validate($valeur, $constraint);

        $this->buildViolation($constraint->message)
            ->setInvalidValue($valeur)
            ->assertRaised();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideFormatsInvalides(): iterable
    {
        yield 'trop court' => ['AB12'];
        yield 'que des lettres' => ['ABCDEFG'];
        yield 'que des chiffres' => ['12345678'];
    }
}
