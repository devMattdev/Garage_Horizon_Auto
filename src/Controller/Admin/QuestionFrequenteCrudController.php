<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\QuestionFrequente;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<QuestionFrequente>
 */
class QuestionFrequenteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuestionFrequente::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Question fréquente')
            ->setEntityLabelInPlural('FAQ')
            ->setDefaultSort(['ordre' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('question');
        yield TextareaField::new('reponse');
        yield TextField::new('categorie')->setRequired(false);
        yield IntegerField::new('ordre');
        yield BooleanField::new('actif');
    }
}
