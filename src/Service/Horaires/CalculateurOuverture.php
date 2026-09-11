<?php

declare(strict_types=1);

namespace App\Service\Horaires;

use App\Entity\PlageHoraire;
use App\Enum\JourSemaine;
use App\Repository\FermetureExceptionnelleRepository;
use App\Repository\PlageHoraireRepository;

/**
 * Détermine si le garage est ouvert à un instant donné, et le prochain créneau
 * d'ouverture (RG-11, back.md section 9).
 */
class CalculateurOuverture
{
    /** Jours fériés français fixes (mm-dd) — hors jours mobiles calculés séparément. */
    private const JOURS_FERIES_FIXES = ['01-01', '05-01', '05-08', '07-14', '08-15', '11-01', '11-11', '12-25'];

    private const HORIZON_JOURS = 14;

    public function __construct(
        private readonly PlageHoraireRepository $plageHoraireRepository,
        private readonly FermetureExceptionnelleRepository $fermetureExceptionnelleRepository,
    ) {
    }

    public function estOuvert(\DateTimeImmutable $instant): bool
    {
        if ($this->estFerme($instant)) {
            return false;
        }

        $plage = $this->plageDuJour($instant);

        if (null === $plage || $plage->isFerme()) {
            return false;
        }

        $heure = $instant->format('H:i:s');

        return $this->heureDansCreneau($heure, $plage->getMatinDebut(), $plage->getMatinFin())
            || $this->heureDansCreneau($heure, $plage->getApremDebut(), $plage->getApremFin());
    }

    public function prochaineOuverture(\DateTimeImmutable $depuis): ?\DateTimeImmutable
    {
        for ($decalage = 0; $decalage <= self::HORIZON_JOURS; ++$decalage) {
            $jourCourant = $depuis->modify(\sprintf('+%d days', $decalage));

            if ($this->estFerme($jourCourant)) {
                continue;
            }

            $plage = $this->plageDuJour($jourCourant);

            if (null === $plage || $plage->isFerme()) {
                continue;
            }

            foreach ([$plage->getMatinDebut(), $plage->getApremDebut()] as $debutCreneau) {
                if (null === $debutCreneau) {
                    continue;
                }

                $ouverture = $jourCourant->setTime(
                    (int) $debutCreneau->format('H'),
                    (int) $debutCreneau->format('i'),
                );

                if ($ouverture >= $depuis) {
                    return $ouverture;
                }
            }
        }

        return null;
    }

    private function plageDuJour(\DateTimeImmutable $instant): ?PlageHoraire
    {
        $plages = $this->plageHoraireRepository->findToutesIndexeesParJour();
        $jour = JourSemaine::from((int) $instant->format('N'));

        return $plages[$jour->value] ?? null;
    }

    private function heureDansCreneau(string $heure, ?\DateTimeImmutable $debut, ?\DateTimeImmutable $fin): bool
    {
        if (null === $debut || null === $fin) {
            return false;
        }

        return $heure >= $debut->format('H:i:s') && $heure <= $fin->format('H:i:s');
    }

    private function estFerme(\DateTimeImmutable $instant): bool
    {
        return $this->estJourFerie($instant)
            || null !== $this->fermetureExceptionnelleRepository->trouverCouvrant($instant);
    }

    private function estJourFerie(\DateTimeImmutable $instant): bool
    {
        if (\in_array($instant->format('m-d'), self::JOURS_FERIES_FIXES, true)) {
            return true;
        }

        $paques = $this->datePaques((int) $instant->format('Y'));

        $joursFeriesMobiles = [
            $paques->modify('+1 day')->format('Y-m-d'),
            $paques->modify('+39 days')->format('Y-m-d'),
            $paques->modify('+50 days')->format('Y-m-d'),
        ];

        return \in_array($instant->format('Y-m-d'), $joursFeriesMobiles, true);
    }

    /**
     * Algorithme de Gauss (sans dépendance à ext-calendar).
     */
    private function datePaques(int $annee): \DateTimeImmutable
    {
        $a = $annee % 19;
        $b = intdiv($annee, 100);
        $c = $annee % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mois = intdiv($h + $l - 7 * $m + 114, 31);
        $jour = (($h + $l - 7 * $m + 114) % 31) + 1;

        return new \DateTimeImmutable(\sprintf('%04d-%02d-%02d', $annee, $mois, $jour));
    }
}
