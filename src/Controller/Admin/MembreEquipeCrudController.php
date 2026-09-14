<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MembreEquipe;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends GerantSeulementCrudController<MembreEquipe>
 */
class MembreEquipeCrudController extends GerantSeulementCrudController
{
    public static function getEntityFqcn(): string
    {
        return MembreEquipe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Membre de l\'équipe')
            ->setEntityLabelInPlural('Équipe')
            ->setDefaultSort(['ordre' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom');
        yield TextField::new('fonction');
        yield ImageField::new('photo')
            ->setBasePath('/uploads/equipe')
            ->setUploadDir('public/uploads/equipe')
            ->setRequired(false);
        yield IntegerField::new('ordre');
        yield BooleanField::new('actif');
    }
}
