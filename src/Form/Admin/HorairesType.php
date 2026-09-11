<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array{plages: array<int, \App\Entity\PlageHoraire>}>
 */
class HorairesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plages', CollectionType::class, [
            'entry_type' => PlageHoraireType::class,
            'allow_add' => false,
            'allow_delete' => false,
            'by_reference' => true,
        ]);
    }
}
