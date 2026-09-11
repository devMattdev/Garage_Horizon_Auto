<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\PlageHoraire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<PlageHoraire>
 */
class PlageHoraireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matinDebut', TimeType::class, ['required' => false, 'widget' => 'single_text'])
            ->add('matinFin', TimeType::class, ['required' => false, 'widget' => 'single_text'])
            ->add('apremDebut', TimeType::class, ['required' => false, 'widget' => 'single_text'])
            ->add('apremFin', TimeType::class, ['required' => false, 'widget' => 'single_text'])
            ->add('ferme', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PlageHoraire::class]);
    }
}
