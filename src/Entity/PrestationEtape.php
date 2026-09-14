<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PrestationEtapeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Élément inclus dans une prestation (ex. « Vidange moteur » pour la révision),
 * affiché avec son propre descriptif et, si renseigné, son propre tarif.
 */
#[ORM\Entity(repositoryClass: PrestationEtapeRepository::class)]
#[ORM\Table(name: 'prestation_etape')]
class PrestationEtape
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Prestation::class, inversedBy: 'etapes')]
    #[ORM\JoinColumn(nullable: false)]
    private Prestation $prestation;

    #[ORM\Column(length: 120)]
    private string $titre;

    #[ORM\Column(length: 320)]
    private string $description;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?string $prix = null;

    #[ORM\Column(length: 40)]
    private string $icone = 'check';

    #[ORM\Column]
    private int $ordre = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrestation(): Prestation
    {
        return $this->prestation;
    }

    public function setPrestation(Prestation $prestation): static
    {
        $this->prestation = $prestation;

        return $this;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(?string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getIcone(): string
    {
        return $this->icone;
    }

    public function setIcone(string $icone): static
    {
        $this->icone = $icone;

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

    public function __toString(): string
    {
        return $this->titre ?? 'Élément de prestation';
    }
}
