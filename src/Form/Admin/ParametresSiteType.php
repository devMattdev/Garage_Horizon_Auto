<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\ParametresSite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<ParametresSite>
 */
class ParametresSiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('raisonSociale', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('adresse', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('codePostal', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('ville', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('telephone', TelType::class, ['constraints' => [new NotBlank()]])
            ->add('email', EmailType::class, ['constraints' => [new NotBlank()]])
            ->add('siret', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('latitude', TextType::class, ['required' => false])
            ->add('longitude', TextType::class, ['required' => false])
            ->add('urlFicheGoogle', UrlType::class, ['required' => false])
            ->add('urlLeboncoin', UrlType::class, ['required' => false])
            ->add('numeroWhatsapp', TelType::class, ['required' => false])
            ->add('urlFacebook', UrlType::class, ['required' => false])
            ->add('urlInstagram', UrlType::class, ['required' => false])
            ->add('delaiReponseJoursOuvres', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ParametresSite::class]);
    }
}
