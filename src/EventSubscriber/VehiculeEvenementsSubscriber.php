<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\VehiculePublieEvent;
use App\Event\VehiculeVenduEvent;
use App\Service\Seo\GenerateurSitemap;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VehiculePublie -> invalidation du cache HTTP + mise à jour du sitemap.
 * VehiculeVendu -> le retrait à 30 jours est effectué par la tâche planifiée quotidienne (back.md section 11).
 */
class VehiculeEvenementsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly GenerateurSitemap $generateurSitemap,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VehiculePublieEvent::class => 'onVehiculePublie',
            VehiculeVenduEvent::class => 'onVehiculeVendu',
        ];
    }

    public function onVehiculePublie(VehiculePublieEvent $event): void
    {
        $this->generateurSitemap->generer();

        $this->logger->info('Véhicule publié : {reference}.', ['reference' => $event->vehicule->getReference()]);
    }

    public function onVehiculeVendu(VehiculeVenduEvent $event): void
    {
        $this->logger->info('Véhicule vendu : {reference}, retrait programmé dans 30 jours.', [
            'reference' => $event->vehicule->getReference(),
        ]);
    }
}
