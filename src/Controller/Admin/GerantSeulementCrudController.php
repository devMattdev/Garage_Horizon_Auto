<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Security\Voter\GerantVoter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * Base commune aux écrans EasyAdmin réservés au Gérant (back.md section 8) :
 * Prestations, FAQ, Pages locales, Fermetures exceptionnelles, Utilisateurs.
 *
 * @template TEntity of object
 *
 * @extends AbstractCrudController<TEntity>
 */
abstract class GerantSeulementCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityPermission(GerantVoter::RESERVE_GERANT);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->setPermissions([
            Action::INDEX => GerantVoter::RESERVE_GERANT,
            Action::DETAIL => GerantVoter::RESERVE_GERANT,
            Action::NEW => GerantVoter::RESERVE_GERANT,
            Action::EDIT => GerantVoter::RESERVE_GERANT,
            Action::DELETE => GerantVoter::RESERVE_GERANT,
            Action::BATCH_DELETE => GerantVoter::RESERVE_GERANT,
        ]);
    }
}
