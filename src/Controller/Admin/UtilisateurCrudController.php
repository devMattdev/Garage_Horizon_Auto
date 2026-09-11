<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @extends AbstractCrudController<Utilisateur>
 */
class UtilisateurCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom');
        yield EmailField::new('email');
        yield ChoiceField::new('roles')
            ->setChoices([
                'Gérant' => Utilisateur::ROLE_ADMIN,
                'Secrétariat' => Utilisateur::ROLE_SECRETARIAT,
            ])
            ->allowMultipleChoices();
        yield TextField::new('motDePasseEnClair', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => Crud::PAGE_NEW === $pageName,
                'constraints' => Crud::PAGE_NEW === $pageName
                    ? [new NotBlank(), new Length(min: 12)]
                    : [new Callback(static function (?string $valeur, ExecutionContextInterface $context): void {
                        if (null !== $valeur && '' !== $valeur && \strlen($valeur) < 12) {
                            $context->buildViolation('Le mot de passe doit contenir au moins 12 caractères.')
                                ->addViolation();
                        }
                    })],
            ])
            ->onlyWhenUpdating()
            ->setHelp('Laisser vide pour ne pas modifier le mot de passe actuel.')
            ->hideOnIndex();
        yield TextField::new('motDePasseEnClair', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions(['mapped' => false, 'constraints' => [new NotBlank(), new Length(min: 12)]])
            ->onlyWhenCreating();
        yield BooleanField::new('actif');
        yield DateTimeField::new('derniereConnexionLe')->hideOnForm();
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->appliquerMotDePasse($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->appliquerMotDePasse($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function appliquerMotDePasse(object $entityInstance): void
    {
        if (!$entityInstance instanceof Utilisateur) {
            return;
        }

        $motDePasseEnClair = $this->getContext()?->getRequest()->request->all('Utilisateur')['motDePasseEnClair'] ?? null;

        if (\is_string($motDePasseEnClair) && '' !== $motDePasseEnClair) {
            $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $motDePasseEnClair));
        }
    }
}
