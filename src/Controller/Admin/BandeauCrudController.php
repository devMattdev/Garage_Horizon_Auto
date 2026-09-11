<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Bandeau;
use App\Enum\NiveauBandeau;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Bandeau>
 */
class BandeauCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Bandeau::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Bandeau')
            ->setEntityLabelInPlural('Bandeaux');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('message');
        yield ChoiceField::new('niveau')->setChoices(array_combine(
            array_map(static fn (NiveauBandeau $n) => $n->value, NiveauBandeau::cases()),
            NiveauBandeau::cases(),
        ));
        yield DateTimeField::new('debutLe')->setRequired(false);
        yield DateTimeField::new('finLe')->setRequired(false);
        yield BooleanField::new('actif');
    }
}
