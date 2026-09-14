<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MembreEquipeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Membre de l'équipe affiché sur la page « Le garage » (photo, nom, fonction).
 */
#[ORM\Entity(repositoryClass: MembreEquipeRepository::class)]
#[ORM\Table(name: 'membre_equipe')]
class MembreEquipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $nom;

    #[ORM\Column(length: 120)]
    private string $fonction;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $actif = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getFonction(): string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? 'Membre de l\'équipe';
    }
}
