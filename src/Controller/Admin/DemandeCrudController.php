<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Demande;
use App\Entity\JournalAudit;
use App\Entity\Utilisateur;
use App\Enum\StatutDemande;
use App\Enum\TypeDemande;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractCrudController<Demande>
 */
class DemandeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Target('audit')]
        private readonly LoggerInterface $auditLogger,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Demande::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Demande')
            ->setEntityLabelInPlural('Demandes')
            ->setDefaultSort(['creeLe' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::new('exportCsv', 'Exporter en CSV', 'fa fa-file-csv')
                ->linkToRoute('admin_demande_export_csv')
                ->createAsGlobalAction());
    }

    public function configureFields(string $pageName): iterable
    {
        yield ChoiceField::new('type')->setChoices(array_combine(
            array_map(static fn (TypeDemande $t) => $t->value, TypeDemande::cases()),
            TypeDemande::cases(),
        ))->renderAsBadges();
        yield TextField::new('nom');
        yield TelephoneField::new('telephone')->setRequired(false);
        yield TextField::new('email')->setRequired(false);
        yield TextField::new('immatriculation')->setRequired(false)->hideOnIndex();
        yield IntegerField::new('kilometrage')->setRequired(false)->hideOnIndex();
        yield AssociationField::new('prestation')->setRequired(false)->hideOnIndex();
        yield AssociationField::new('vehicule')->setRequired(false)->hideOnIndex();
        yield TextField::new('creneauSouhaite')->setRequired(false)->hideOnIndex();
        yield TextareaField::new('message')->setRequired(false)->hideOnIndex();
        yield BooleanField::new('consentementRelance')->hideOnIndex();
        yield ChoiceField::new('statut')->setChoices(array_combine(
            array_map(static fn (StatutDemande $s) => $s->value, StatutDemande::cases()),
            StatutDemande::cases(),
        ))->renderAsBadges();
        yield DateTimeField::new('creeLe')->hideOnForm();
        yield DateTimeField::new('rdvConfirmeLe')->setRequired(false);
        yield DateTimeField::new('rappelEnvoyeLe')->hideOnForm();
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        $this->journaliserAcces($context, 'consultation');

        return parent::detail($context);
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        $this->journaliserAcces($context, 'modification');

        return parent::edit($context);
    }

    /**
     * @param AdminContext<Demande> $context
     */
    private function journaliserAcces(AdminContext $context, string $action): void
    {
        $demande = $context->getEntity()->getInstance();

        if (!$demande instanceof Demande) {
            return;
        }

        $utilisateur = $this->getUser();

        $journal = new JournalAudit();
        $journal->setUtilisateur($utilisateur instanceof Utilisateur ? $utilisateur : null);
        $journal->setAction($action);
        $journal->setEntite('Demande');
        $journal->setEntiteId($demande->getId());

        $this->entityManager->persist($journal);
        $this->entityManager->flush();

        $this->auditLogger->info($action, [
            'demande' => $demande->getId(),
            'utilisateur' => $utilisateur instanceof Utilisateur ? $utilisateur->getId() : null,
        ]);
    }
}
