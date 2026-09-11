<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Prestation;
use App\Enum\FamillePrestation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Prestation>
 */
class PrestationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Prestation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Prestation')
            ->setEntityLabelInPlural('Prestations')
            ->setDefaultSort(['ordre' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom');
        yield SlugField::new('slug')->setTargetFieldName('nom');
        yield ChoiceField::new('famille')->setChoices(array_combine(
            array_map(static fn (FamillePrestation $f) => $f->value, FamillePrestation::cases()),
            FamillePrestation::cases(),
        ));
        yield TextareaField::new('resume');
        yield TextareaField::new('description')->hideOnIndex();
        yield MoneyField::new('tarifIndicatif')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2)->setRequired(false);
        yield TextField::new('mentionTarif')->setRequired(false)->hideOnIndex();
        yield BooleanField::new('miseEnAvant');
        yield IntegerField::new('ordre');
        yield BooleanField::new('actif');
        yield ImageField::new('image')->setBasePath('/uploads/prestations')->setUploadDir('public/uploads/prestations')->setRequired(false)->hideOnIndex();
        yield TextField::new('seoTitre')->setRequired(false)->hideOnIndex();
        yield TextareaField::new('seoDescription')->setRequired(false)->hideOnIndex();
    }
}
