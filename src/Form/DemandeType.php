<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Demande;
use App\Entity\Prestation;
use App\Entity\Vehicule;
use App\Enum\TypeDemande;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Formulaire public de demande (RDV, devis, question, véhicule) — back.md section 5.3.
 * Inclut le champ honeypot "siteWeb" : invisible pour un humain, rempli uniquement par les robots.
 */
/**
 * @extends AbstractType<Demande>
 */
class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EnumType::class, [
                'class' => TypeDemande::class,
                'choice_label' => static fn (TypeDemande $type) => match ($type) {
                    TypeDemande::RDV => 'Prendre rendez-vous',
                    TypeDemande::DEVIS => 'Demander un devis',
                    TypeDemande::QUESTION => 'Poser une question',
                    TypeDemande::VEHICULE => 'Un véhicule d\'occasion m\'intéresse',
                },
            ])
            ->add('nom', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('telephone', TelType::class, ['required' => false])
            ->add('email', EmailType::class, [
                'required' => false,
                'constraints' => [new Email()],
            ])
            ->add('immatriculation', TextType::class, ['required' => false])
            ->add('kilometrage', IntegerType::class, ['required' => false])
            ->add('prestation', EntityType::class, [
                'class' => Prestation::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Aucune prestation en particulier',
            ])
            ->add('vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'choice_label' => 'titre',
                'required' => false,
                'placeholder' => 'Aucun véhicule en particulier',
            ])
            ->add('creneauSouhaite', TextType::class, ['required' => false, 'label' => 'Créneau souhaité'])
            ->add('message', TextareaType::class, ['required' => false])
            ->add('consentementRelance', CheckboxType::class, [
                'required' => false,
                'label' => 'J\'accepte de recevoir des offres et relances commerciales par courriel.',
            ])
            ->add('siteWeb', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false,
                'constraints' => [new Blank(message: 'Champ invalide.')],
            ])
            ->add('cfTurnstileResponse', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
            'constraints' => [
                new Callback(
                    static function (Demande $demande, ExecutionContextInterface $context): void {
                        if (null === $demande->getTelephone() && null === $demande->getEmail()) {
                            $context->buildViolation('Merci de renseigner un téléphone ou un courriel.')
                                ->atPath('telephone')
                                ->addViolation();
                        }
                    },
                ),
            ],
        ]);
    }
}
