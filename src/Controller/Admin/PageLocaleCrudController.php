<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PageLocale;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<PageLocale>
 */
class PageLocaleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PageLocale::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Page locale')
            ->setEntityLabelInPlural('Pages locales');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('commune');
        yield TextField::new('titre');
        yield SlugField::new('slug')->setTargetFieldName('titre');
        yield TextEditorField::new('contenu');
        yield TextField::new('seoTitre')->setRequired(false)->hideOnIndex();
        yield TextareaField::new('seoDescription')->setRequired(false)->hideOnIndex();
        yield BooleanField::new('actif');
    }
}
