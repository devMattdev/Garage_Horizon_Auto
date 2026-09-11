<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Horaires;

use App\Entity\FermetureExceptionnelle;
use App\Entity\PlageHoraire;
use App\Enum\JourSemaine;
use App\Repository\FermetureExceptionnelleRepository;
use App\Repository\PlageHoraireRepository;
use App\Service\Horaires\CalculateurOuverture;
use PHPUnit\Framework\TestCase;

final class CalculateurOuvertureTest extends TestCase
{
    private function creerPlage(JourSemaine $jour, ?string $matinDebut, ?string $matinFin, ?string $apremDebut, ?string $apremFin, bool $ferme = false): PlageHoraire
    {
        $plage = new PlageHoraire();
        $plage->setJour($jour);
        $plage->setFerme($ferme);
        $plage->setMatinDebut(null !== $matinDebut ? new \DateTimeImmutable($matinDebut) : null);
        $plage->setMatinFin(null !== $matinFin ? new \DateTimeImmutable($matinFin) : null);
        $plage->setApremDebut(null !== $apremDebut ? new \DateTimeImmutable($apremDebut) : null);
        $plage->setApremFin(null !== $apremFin ? new \DateTimeImmutable($apremFin) : null);

        return $plage;
    }

    /**
     * @param array<int, PlageHoraire> $plagesParJour
     */
    private function creerCalculateur(array $plagesParJour, ?FermetureExceptionnelle $fermetureCouvrante = null): CalculateurOuverture
    {
        $plageHoraireRepository = $this->createMock(PlageHoraireRepository::class);
        $plageHoraireRepository->method('findToutesIndexeesParJour')->willReturn($plagesParJour);

        $fermetureExceptionnelleRepository = $this->createMock(FermetureExceptionnelleRepository::class);
        $fermetureExceptionnelleRepository->method('trouverCouvrant')->willReturn($fermetureCouvrante);

        return new CalculateurOuverture($plageHoraireRepository, $fermetureExceptionnelleRepository);
    }

    public function testOuvertPendantLaPlageDuMatin(): void
    {
        // Lundi 8 septembre 2025 10h00 (jour ouvré, hors jour férié) : ouvert 8h-12h / 14h-18h le lundi.
        $calculateur = $this->creerCalculateur([
            JourSemaine::LUNDI->value => $this->creerPlage(JourSemaine::LUNDI, '08:00', '12:00', '14:00', '18:00'),
        ]);

        self::assertTrue($calculateur->estOuvert(new \DateTimeImmutable('2025-09-08 10:00')));
    }

    public function testFermeEntreLesDeuxPlages(): void
    {
        $calculateur = $this->creerCalculateur([
            JourSemaine::LUNDI->value => $this->creerPlage(JourSemaine::LUNDI, '08:00', '12:00', '14:00', '18:00'),
        ]);

        self::assertFalse($calculateur->estOuvert(new \DateTimeImmutable('2025-09-08 13:00')));
    }

    public function testFermeLeJourMarqueFerme(): void
    {
        $calculateur = $this->creerCalculateur([
            JourSemaine::DIMANCHE->value => $this->creerPlage(JourSemaine::DIMANCHE, null, null, null, null, ferme: true),
        ]);

        self::assertFalse($calculateur->estOuvert(new \DateTimeImmutable('2025-06-08 10:00')));
    }

    public function testFermeUnJourFerieMemeSiDansLaPlageHoraire(): void
    {
        // 1er mai 2025 (jeudi) : jour férié fixe.
        $calculateur = $this->creerCalculateur([
            JourSemaine::JEUDI->value => $this->creerPlage(JourSemaine::JEUDI, '08:00', '12:00', '14:00', '18:00'),
        ]);

        self::assertFalse($calculateur->estOuvert(new \DateTimeImmutable('2025-05-01 10:00')));
    }

    public function testFermeLeLundiDePaques(): void
    {
        // Pâques 2025 : dimanche 20 avril -> lundi de Pâques le 21 avril.
        $calculateur = $this->creerCalculateur([
            JourSemaine::LUNDI->value => $this->creerPlage(JourSemaine::LUNDI, '08:00', '12:00', '14:00', '18:00'),
        ]);

        self::assertFalse($calculateur->estOuvert(new \DateTimeImmutable('2025-04-21 10:00')));
    }

    public function testFermeDurantUneFermetureExceptionnelle(): void
    {
        $fermeture = new FermetureExceptionnelle();
        $fermeture->setLibelle('Congés annuels');
        $fermeture->setDebutLe(new \DateTimeImmutable('2025-08-01'));
        $fermeture->setFinLe(new \DateTimeImmutable('2025-08-15'));

        $calculateur = $this->creerCalculateur([
            JourSemaine::LUNDI->value => $this->creerPlage(JourSemaine::LUNDI, '08:00', '12:00', '14:00', '18:00'),
        ], fermetureCouvrante: $fermeture);

        self::assertFalse($calculateur->estOuvert(new \DateTimeImmutable('2025-08-04 10:00')));
    }

    public function testProchaineOuvertureTrouveLeCreneauSuivant(): void
    {
        $calculateur = $this->creerCalculateur([
            JourSemaine::LUNDI->value => $this->creerPlage(JourSemaine::LUNDI, '08:00', '12:00', '14:00', '18:00'),
        ]);

        // Lundi 13h : la plage du matin est passée, l'après-midi n'a pas commencé.
        $prochaine = $calculateur->prochaineOuverture(new \DateTimeImmutable('2025-09-08 13:00'));

        self::assertNotNull($prochaine);
        self::assertSame('2025-09-08 14:00', $prochaine->format('Y-m-d H:i'));
    }

    public function testProchaineOuvertureRenvoieNullSiAucunCreneauDansHorizon(): void
    {
        $calculateur = $this->creerCalculateur([]);

        self::assertNull($calculateur->prochaineOuverture(new \DateTimeImmutable('2025-09-08 13:00')));
    }
}
