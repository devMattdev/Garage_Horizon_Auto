<?php

declare(strict_types=1);

namespace App\Service\Horaires;

use App\Entity\PlageHoraire;
use App\Enum\JourSemaine;
use App\Repository\PlageHoraireRepository;

/**
 * Condense les horaires hebdomadaires en un résumé compact (ex. « Lun - Ven : 8h-12h / 13h30-18h »)
 * en regroupant les jours consécutifs partageant les mêmes horaires. Les jours fermés sont omis.
 */
class ResumeHoraires
{
    public function __construct(
        private readonly PlageHoraireRepository $plageHoraireRepository,
    ) {
    }

    public function resume(): ?string
    {
        $parties = [];
        foreach ($this->groupes() as $groupe) {
            $parties[] = $groupe['jours'].' : '.$groupe['horaires'];
        }

        return [] !== $parties ? implode(' · ', $parties) : null;
    }

    /**
     * @return list<array{jours: string, horaires: string}> les jours fermés sont omis
     */
    public function groupes(): array
    {
        $plages = $this->plageHoraireRepository->findToutesIndexeesParJour();

        if ([] === $plages) {
            return [];
        }

        $groupes = [];
        $courant = null;

        foreach (range(1, 7) as $jourValeur) {
            $texte = $this->formaterPlage($plages[$jourValeur] ?? null);
            $libelle = JourSemaine::from($jourValeur)->libelle();

            if (null !== $courant && $courant['texte'] === $texte) {
                $courant['fin'] = $libelle;
            } else {
                if (null !== $courant) {
                    $groupes[] = $courant;
                }
                $courant = ['debut' => $libelle, 'fin' => $libelle, 'texte' => $texte];
            }
        }
        if (null !== $courant) {
            $groupes[] = $courant;
        }

        $resultat = [];
        foreach ($groupes as $groupe) {
            if (null === $groupe['texte']) {
                continue;
            }

            $resultat[] = [
                'jours' => $groupe['debut'] === $groupe['fin'] ? $groupe['debut'] : $groupe['debut'].' - '.$groupe['fin'],
                'horaires' => $groupe['texte'],
            ];
        }

        return $resultat;
    }

    private function formaterPlage(?PlageHoraire $plage): ?string
    {
        if (null === $plage || $plage->isFerme()) {
            return null;
        }

        $segments = [];
        if (null !== $plage->getMatinDebut() && null !== $plage->getMatinFin()) {
            $segments[] = $this->formaterHeure($plage->getMatinDebut()).'-'.$this->formaterHeure($plage->getMatinFin());
        }
        if (null !== $plage->getApremDebut() && null !== $plage->getApremFin()) {
            $segments[] = $this->formaterHeure($plage->getApremDebut()).'-'.$this->formaterHeure($plage->getApremFin());
        }

        return [] !== $segments ? implode(' / ', $segments) : null;
    }

    private function formaterHeure(\DateTimeImmutable $heure): string
    {
        return '00' === $heure->format('i') ? $heure->format('G\h') : $heure->format('G\hi');
    }
}
