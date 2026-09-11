<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PhotoVehiculeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotoVehiculeRepository::class)]
#[ORM\Table(name: 'photo_vehicule')]
class PhotoVehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vehicule::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private Vehicule $vehicule;

    #[ORM\Column(length: 255)]
    private string $fichier;

    #[ORM\Column(length: 255)]
    private string $texteAlternatif;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $principale = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicule(): Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(Vehicule $vehicule): static
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    public function getFichier(): string
    {
        return $this->fichier;
    }

    public function setFichier(string $fichier): static
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getTexteAlternatif(): string
    {
        return $this->texteAlternatif;
    }

    public function setTexteAlternatif(string $texteAlternatif): static
    {
        $this->texteAlternatif = $texteAlternatif;

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

    public function isPrincipale(): bool
    {
        return $this->principale;
    }

    public function setPrincipale(bool $principale): static
    {
        $this->principale = $principale;

        return $this;
    }

    public function __toString(): string
    {
        return $this->texteAlternatif ?? 'Photo de véhicule';
    }
}
