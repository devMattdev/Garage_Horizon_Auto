<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\FermetureExceptionnelle;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<FermetureExceptionnelle>
 */
class FermetureExceptionnelleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FermetureExceptionnelle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Fermeture exceptionnelle')
            ->setEntityLabelInPlural('Fermetures exceptionnelles')
            ->setDefaultSort(['debutLe' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('libelle');
        yield DateField::new('debutLe');
        yield DateField::new('finLe');
    }
}
