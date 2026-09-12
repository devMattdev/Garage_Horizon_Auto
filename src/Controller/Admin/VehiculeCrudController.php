<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Vehicule;
use App\Enum\BoiteVitesses;
use App\Enum\Energie;
use App\Enum\StatutVehicule;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<Vehicule>
 */
class VehiculeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vehicule::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Véhicule')
            ->setEntityLabelInPlural('Véhicules d\'occasion')
            ->setDefaultSort(['publieLe' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::new('exportCsv', 'Exporter en CSV', 'fa fa-file-csv')
            ->linkToRoute('admin_vehicule_export_csv')
            ->createAsGlobalAction());
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reference');
        yield TextField::new('titre');
        yield TextField::new('marque');
        yield TextField::new('modele');
        yield TextField::new('version')->setRequired(false);
        yield DateField::new('misEnCirculationLe');
        yield IntegerField::new('kilometrage');
        yield ChoiceField::new('energie')->setChoices(array_combine(
            array_map(static fn (Energie $e) => $e->value, Energie::cases()),
            Energie::cases(),
        ));
        yield ChoiceField::new('boite')->setChoices(array_combine(
            array_map(static fn (BoiteVitesses $b) => $b->value, BoiteVitesses::cases()),
            BoiteVitesses::cases(),
        ));
        yield IntegerField::new('puissanceFiscale')->setRequired(false)->hideOnIndex();
        yield IntegerField::new('puissanceDin')->setRequired(false)->hideOnIndex();
        yield MoneyField::new('prix')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2)->setRequired(false);
        yield TextareaField::new('description')->hideOnIndex();
        yield ChoiceField::new('statut')->setChoices(array_combine(
            array_map(static fn (StatutVehicule $s) => $s->value, StatutVehicule::cases()),
            StatutVehicule::cases(),
        ));
        yield UrlField::new('urlAnnonceExterne')->setRequired(false)->hideOnIndex();
        yield DateTimeField::new('publieLe')->setRequired(false)->hideOnForm();
        yield DateTimeField::new('modifieLe')->hideOnForm();
        yield CollectionField::new('photos')->onlyOnDetail();
    }
}
