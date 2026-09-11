<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Demande;
use App\Enum\StatutDemande;
use App\Repository\DemandeRepository;
use App\Repository\VehiculeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin_dashboard')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly DemandeRepository $demandeRepository,
        private readonly VehiculeRepository $vehiculeRepository,
    ) {
    }

    public function index(): Response
    {
        $debutSemaine = (new \DateTimeImmutable('monday this week'))->setTime(0, 0);

        $demandesNonTraitees = array_filter(
            $this->demandeRepository->findAll(),
            static fn (Demande $d) => \in_array($d->getStatut(), [StatutDemande::NOUVELLE, StatutDemande::EN_COURS], true),
        );

        return $this->render('admin/dashboard.html.twig', [
            'demandes_non_traitees' => \count($demandesNonTraitees),
            'demandes_semaine' => \count(array_filter(
                $this->demandeRepository->findAll(),
                static fn (Demande $d) => $d->getCreeLe() >= $debutSemaine,
            )),
            'vehicules_obsoletes' => \count($this->vehiculeRepository->findPubliesSansModificationDepuis(
                (new \DateTimeImmutable())->modify('-60 days'),
            )),
            'prochains_rendez_vous' => array_filter(
                $this->demandeRepository->findAll(),
                static fn (Demande $d) => null !== $d->getRdvConfirmeLe() && $d->getRdvConfirmeLe() >= new \DateTimeImmutable(),
            ),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Horizon Auto — back-office')
            ->setLocales(['fr']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-gauge');
        yield MenuItem::linkTo(DemandeCrudController::class, 'Demandes', 'fa fa-envelope');
        yield MenuItem::linkTo(PrestationCrudController::class, 'Prestations', 'fa fa-screwdriver-wrench');
        yield MenuItem::linkTo(VehiculeCrudController::class, 'Véhicules d\'occasion', 'fa fa-car');
        yield MenuItem::linkTo(PhotoVehiculeCrudController::class, 'Photos des véhicules', 'fa fa-images');
        yield MenuItem::linkTo(QuestionFrequenteCrudController::class, 'FAQ', 'fa fa-circle-question');
        yield MenuItem::linkTo(PageLocaleCrudController::class, 'Pages locales', 'fa fa-location-dot');
        yield MenuItem::linkToRoute('Horaires', 'fa fa-clock', 'admin_horaires');
        yield MenuItem::linkTo(FermetureExceptionnelleCrudController::class, 'Fermetures', 'fa fa-door-closed');
        yield MenuItem::linkTo(BandeauCrudController::class, 'Bandeau', 'fa fa-bullhorn');
        yield MenuItem::linkToRoute('Paramètres du site', 'fa fa-gear', 'admin_parametres');
        yield MenuItem::linkTo(UtilisateurCrudController::class, 'Utilisateurs', 'fa fa-users');
        yield MenuItem::linkToRoute('Export RGPD', 'fa fa-file-export', 'admin_rgpd_export');
    }
}
