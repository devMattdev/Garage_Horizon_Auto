<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\NiveauBandeau;
use App\Repository\BandeauRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BandeauRepository::class)]
#[ORM\Table(name: 'bandeau')]
class Bandeau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $message;

    #[ORM\Column(enumType: NiveauBandeau::class)]
    private NiveauBandeau $niveau = NiveauBandeau::INFO;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $debutLe = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $finLe = null;

    #[ORM\Column]
    private bool $actif = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getNiveau(): NiveauBandeau
    {
        return $this->niveau;
    }

    public function setNiveau(NiveauBandeau $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getDebutLe(): ?\DateTimeImmutable
    {
        return $this->debutLe;
    }

    public function setDebutLe(?\DateTimeImmutable $debutLe): static
    {
        $this->debutLe = $debutLe;

        return $this;
    }

    public function getFinLe(): ?\DateTimeImmutable
    {
        return $this->finLe;
    }

    public function setFinLe(?\DateTimeImmutable $finLe): static
    {
        $this->finLe = $finLe;

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
}
