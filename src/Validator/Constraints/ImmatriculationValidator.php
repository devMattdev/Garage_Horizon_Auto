<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ImmatriculationValidator extends ConstraintValidator
{
    // SIV (depuis 2009) : AA-123-AA. FNI (avant 2009) : 1 à 4 chiffres, 1 à 3 lettres, 2 ou 3 chiffres.
    private const FORMAT_SIV = '/^[A-Z]{2}\d{3}[A-Z]{2}$/';
    private const FORMAT_FNI = '/^\d{1,4}[A-Z]{1,3}\d{2,3}$/';

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Immatriculation) {
            throw new UnexpectedTypeException($constraint, Immatriculation::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $normalisee = strtoupper(str_replace([' ', '-'], '', $value));

        if (1 === preg_match(self::FORMAT_SIV, $normalisee) || 1 === preg_match(self::FORMAT_FNI, $normalisee)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setInvalidValue($value)
            ->addViolation();
    }
}
