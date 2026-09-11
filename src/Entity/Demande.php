<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatutDemande;
use App\Enum\TypeDemande;
use App\Repository\DemandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
#[ORM\Table(name: 'demande')]
#[ORM\Index(name: 'idx_demande_statut', columns: ['statut'])]
#[ORM\Index(name: 'idx_demande_purge_prevue_le', columns: ['purge_prevue_le'])]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: TypeDemande::class)]
    private TypeDemande $type;

    #[ORM\Column(length: 160)]
    private string $nom;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $immatriculation = null;

    #[ORM\Column(nullable: true)]
    private ?int $kilometrage = null;

    #[ORM\ManyToOne(targetEntity: Prestation::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Prestation $prestation = null;

    #[ORM\ManyToOne(targetEntity: Vehicule::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Vehicule $vehicule = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $creneauSouhaite = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column]
    private bool $consentementRelance = false;

    #[ORM\Column(enumType: StatutDemande::class)]
    private StatutDemande $statut = StatutDemande::NOUVELLE;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $creeLe;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $rdvConfirmeLe = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $rappelEnvoyeLe = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $purgePrevueLe;

    public function __construct()
    {
        $this->creeLe = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): TypeDemande
    {
        return $this->type;
    }

    public function setType(TypeDemande $type): static
    {
        $this->type = $type;

        return $this;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(?string $immatriculation): static
    {
        $this->immatriculation = $immatriculation;

        return $this;
    }

    public function getKilometrage(): ?int
    {
        return $this->kilometrage;
    }

    public function setKilometrage(?int $kilometrage): static
    {
        $this->kilometrage = $kilometrage;

        return $this;
    }

    public function getPrestation(): ?Prestation
    {
        return $this->prestation;
    }

    public function setPrestation(?Prestation $prestation): static
    {
        $this->prestation = $prestation;

        return $this;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(?Vehicule $vehicule): static
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    public function getCreneauSouhaite(): ?string
    {
        return $this->creneauSouhaite;
    }

    public function setCreneauSouhaite(?string $creneauSouhaite): static
    {
        $this->creneauSouhaite = $creneauSouhaite;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isConsentementRelance(): bool
    {
        return $this->consentementRelance;
    }

    public function setConsentementRelance(bool $consentementRelance): static
    {
        $this->consentementRelance = $consentementRelance;

        return $this;
    }

    public function getStatut(): StatutDemande
    {
        return $this->statut;
    }

    public function setStatut(StatutDemande $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function getRdvConfirmeLe(): ?\DateTimeImmutable
    {
        return $this->rdvConfirmeLe;
    }

    public function setRdvConfirmeLe(?\DateTimeImmutable $rdvConfirmeLe): static
    {
        $this->rdvConfirmeLe = $rdvConfirmeLe;

        return $this;
    }

    public function getRappelEnvoyeLe(): ?\DateTimeImmutable
    {
        return $this->rappelEnvoyeLe;
    }

    public function setRappelEnvoyeLe(?\DateTimeImmutable $rappelEnvoyeLe): static
    {
        $this->rappelEnvoyeLe = $rappelEnvoyeLe;

        return $this;
    }

    public function getPurgePrevueLe(): \DateTimeImmutable
    {
        return $this->purgePrevueLe;
    }

    public function setPurgePrevueLe(\DateTimeImmutable $purgePrevueLe): static
    {
        $this->purgePrevueLe = $purgePrevueLe;

        return $this;
    }
}
