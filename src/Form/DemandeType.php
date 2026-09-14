<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Demande;
use App\Entity\Prestation;
use App\Entity\Vehicule;
use App\Enum\StatutVehicule;
use App\Enum\TypeDemande;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\QueryBuilder;
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
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
                'expanded' => true,
                'label' => 'Votre besoin',
                'choice_attr' => static fn () => [
                    'data-formulaire-demande-target' => 'type',
                    'data-action' => 'change->formulaire-demande#updateConditionalFields',
                ],
                'choice_label' => static fn (TypeDemande $type) => match ($type) {
                    TypeDemande::RDV => 'Prendre rendez-vous',
                    TypeDemande::DEVIS => 'Demander un devis',
                    TypeDemande::QUESTION => 'Poser une question',
                    TypeDemande::VEHICULE => 'Un véhicule d’occasion m’intéresse',
                },
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom et prénom',
                'constraints' => [new NotBlank()],
                'attr' => ['autocomplete' => 'name'],
            ])
            ->add('telephone', TelType::class, [
                'required' => false,
                'attr' => ['autocomplete' => 'tel', 'inputmode' => 'tel'],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'constraints' => [new Email()],
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('immatriculation', TextType::class, [
                'required' => false,
                'help' => 'Exemple : AB-123-CD',
                'attr' => ['autocomplete' => 'off', 'inputmode' => 'text'],
            ])
            ->add('kilometrage', IntegerType::class, [
                'required' => false,
                'attr' => ['min' => 0, 'inputmode' => 'numeric'],
            ])
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
                'query_builder' => static fn (VehiculeRepository $repository): QueryBuilder => $repository
                    ->createQueryBuilder('v')
                    ->andWhere('v.statut = :statut')
                    ->setParameter('statut', StatutVehicule::PUBLIE),
            ])
            ->add('creneauSouhaite', TextType::class, [
                'required' => false,
                'label' => 'Créneau souhaité',
                'help' => 'Indiquez un jour et une plage horaire ; le garage vous confirmera le rendez-vous.',
            ])
            ->add('message', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 6,
                    'maxlength' => 2000,
                    'data-formulaire-demande-target' => 'message',
                    'data-action' => 'input->formulaire-demande#updateCounter',
                ],
            ])
            ->add('consentementRelance', CheckboxType::class, [
                'required' => false,
                'label' => 'J’accepte de recevoir des offres et relances commerciales par courriel.',
            ])
            ->add('siteWeb', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Site web',
                'attr' => ['autocomplete' => 'off', 'tabindex' => '-1'],
            ])
            ->add('cfTurnstileResponse', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false,
            ]);
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
