<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PhotoVehicule;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FileField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\File;

/** @extends AbstractCrudController<PhotoVehicule> */
final class PhotoVehiculeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PhotoVehicule::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Photo de véhicule')
            ->setEntityLabelInPlural('Photos des véhicules')
            ->setDefaultSort(['ordre' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('vehicule', 'Véhicule');
        yield FileField::new('fichier', 'Image')
            ->setUploadDir('var/photos/vehicules/source')
            ->setUploadedFileNamePattern('[uuid].[extension]')
            ->setFormTypeOptions(['constraints' => [new File(
                maxSize: '8M',
                extensions: ['jpg', 'jpeg', 'png', 'webp'],
                mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            )]])
            ->onlyWhenCreating();
        yield TextField::new('fichier')->onlyOnIndex();
        yield TextField::new('texteAlternatif', 'Texte alternatif')
            ->setHelp('Décrivez brièvement le véhicule et l’angle de vue.');
        yield IntegerField::new('ordre');
        yield BooleanField::new('principale', 'Photo principale');
    }
}
