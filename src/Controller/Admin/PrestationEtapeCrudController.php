<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PrestationEtape;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends GerantSeulementCrudController<PrestationEtape>
 */
class PrestationEtapeCrudController extends GerantSeulementCrudController
{
    private const ICONES = [
        'oil', 'wrench', 'tire', 'snow', 'scan', 'check',
        'home-oil', 'home-distribution', 'home-brake', 'home-tire', 'home-snow', 'home-engine',
        'shield', 'document', 'badge', 'people', 'clock', 'calendar',
    ];

    public static function getEntityFqcn(): string
    {
        return PrestationEtape::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Élément de prestation')
            ->setEntityLabelInPlural('Éléments des prestations')
            ->setDefaultSort(['prestation' => 'ASC', 'ordre' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('prestation', 'Prestation');
        yield TextField::new('titre');
        yield TextareaField::new('description')->setHelp('Description courte de cet élément (une phrase).');
        yield MoneyField::new('prix')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2)->setRequired(false)
            ->setHelp('Laisser vide si cet élément n’a pas de tarif propre (inclus dans le forfait).');
        yield ChoiceField::new('icone')->setChoices(array_combine(self::ICONES, self::ICONES));
        yield IntegerField::new('ordre');
    }
}
