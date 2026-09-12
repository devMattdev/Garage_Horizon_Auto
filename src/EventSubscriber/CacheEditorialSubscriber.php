<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Cache HTTP d'une heure sur les pages éditoriales publiques en lecture seule
 * (back.md section 12). Restreint à une liste explicite de routes : jamais le
 * formulaire de contact (jeton CSRF par session) ni le back-office.
 *
 * Ne s'applique que si aucune session n'est active sur la requête, pour ne
 * jamais mettre en cache partagé une réponse liée à un visiteur particulier.
 */
final class CacheEditorialSubscriber implements EventSubscriberInterface
{
    private const int DUREE_SECONDES = 3600;

    /**
     * @var list<string>
     */
    private const array ROUTES_CACHEES = [
        'accueil',
        'prestations_index',
        'prestation_detail',
        'faq',
        'page_locale_detail',
        'vehicules_index',
        'vehicule_detail',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->isMethod('GET') || $request->hasPreviousSession()) {
            return;
        }

        $route = $request->attributes->get('_route');
        if (!\is_string($route) || !\in_array($route, self::ROUTES_CACHEES, true)) {
            return;
        }

        $response = $event->getResponse();
        if (!$response->isSuccessful()) {
            return;
        }

        $response->setPublic();
        $response->setMaxAge(self::DUREE_SECONDES);
        $response->setSharedMaxAge(self::DUREE_SECONDES);
    }
}
