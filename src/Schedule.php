<?php

namespace App;

use App\Message\AlerteFichesObsoletesMessage;
use App\Message\PurgeRgpdMessage;
use App\Message\RappelRendezVousMessage;
use App\Message\RegenerationSitemapMessage;
use App\Message\RetraitVehiculesVendusMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Tâches planifiées (back.md section 11) :
 *   - rappels J-1 : quotidien, 9 h
 *   - purge RGPD : quotidien, 3 h
 *   - retrait des véhicules vendus : quotidien
 *   - alerte fiches obsolètes : hebdomadaire
 *   - régénération du sitemap : quotidien
 */
#[AsSchedule]
class Schedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): SymfonySchedule
    {
        return (new SymfonySchedule())
            ->stateful($this->cache) // ensure missed tasks are executed
            ->processOnlyLastMissedRun(true) // ensure only last missed task is run
            ->with(
                RecurringMessage::cron('0 9 * * *', new RappelRendezVousMessage()),
                RecurringMessage::cron('0 3 * * *', new PurgeRgpdMessage()),
                RecurringMessage::cron('30 3 * * *', new RetraitVehiculesVendusMessage()),
                RecurringMessage::cron('0 8 * * 1', new AlerteFichesObsoletesMessage()),
                RecurringMessage::cron('0 4 * * *', new RegenerationSitemapMessage()),
            )
        ;
    }
}
