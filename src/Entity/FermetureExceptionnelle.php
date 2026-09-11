<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FermetureExceptionnelleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FermetureExceptionnelleRepository::class)]
#[ORM\Table(name: 'fermeture_exceptionnelle')]
class FermetureExceptionnelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $debutLe;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $finLe;

    #[ORM\Column(length: 160)]
    private string $libelle;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDebutLe(): \DateTimeImmutable
    {
        return $this->debutLe;
    }

    public function setDebutLe(\DateTimeImmutable $debutLe): static
    {
        $this->debutLe = $debutLe;

        return $this;
    }

    public function getFinLe(): \DateTimeImmutable
    {
        return $this->finLe;
    }

    public function setFinLe(\DateTimeImmutable $finLe): static
    {
        $this->finLe = $finLe;

        return $this;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }
}
