<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\JourSemaine;
use App\Repository\PlageHoraireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlageHoraireRepository::class)]
#[ORM\Table(name: 'plage_horaire')]
#[ORM\UniqueConstraint(name: 'uniq_plage_horaire_jour', fields: ['jour'])]
class PlageHoraire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: JourSemaine::class)]
    private JourSemaine $jour;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $matinDebut = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $matinFin = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $apremDebut = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $apremFin = null;

    #[ORM\Column]
    private bool $ferme = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): JourSemaine
    {
        return $this->jour;
    }

    public function setJour(JourSemaine $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function getMatinDebut(): ?\DateTimeImmutable
    {
        return $this->matinDebut;
    }

    public function setMatinDebut(?\DateTimeImmutable $matinDebut): static
    {
        $this->matinDebut = $matinDebut;

        return $this;
    }

    public function getMatinFin(): ?\DateTimeImmutable
    {
        return $this->matinFin;
    }

    public function setMatinFin(?\DateTimeImmutable $matinFin): static
    {
        $this->matinFin = $matinFin;

        return $this;
    }

    public function getApremDebut(): ?\DateTimeImmutable
    {
        return $this->apremDebut;
    }

    public function setApremDebut(?\DateTimeImmutable $apremDebut): static
    {
        $this->apremDebut = $apremDebut;

        return $this;
    }

    public function getApremFin(): ?\DateTimeImmutable
    {
        return $this->apremFin;
    }

    public function setApremFin(?\DateTimeImmutable $apremFin): static
    {
        $this->apremFin = $apremFin;

        return $this;
    }

    public function isFerme(): bool
    {
        return $this->ferme;
    }

    public function setFerme(bool $ferme): static
    {
        $this->ferme = $ferme;

        return $this;
    }
}
