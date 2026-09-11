<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\BoiteVitesses;
use App\Enum\Energie;
use App\Enum\StatutVehicule;
use App\Repository\VehiculeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
#[ORM\Table(name: 'vehicule')]
#[ORM\UniqueConstraint(name: 'uniq_vehicule_reference', fields: ['reference'])]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $reference;

    #[ORM\Column(length: 180)]
    private string $titre;

    #[ORM\Column(length: 80)]
    private string $marque;

    #[ORM\Column(length: 80)]
    private string $modele;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $misEnCirculationLe;

    #[ORM\Column]
    private int $kilometrage;

    #[ORM\Column(enumType: Energie::class)]
    private Energie $energie;

    #[ORM\Column(enumType: BoiteVitesses::class)]
    private BoiteVitesses $boite;

    #[ORM\Column(nullable: true)]
    private ?int $puissanceFiscale = null;

    #[ORM\Column(nullable: true)]
    private ?int $puissanceDin = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 9, scale: 2, nullable: true)]
    private ?string $prix = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(enumType: StatutVehicule::class)]
    private StatutVehicule $statut = StatutVehicule::BROUILLON;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlAnnonceExterne = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publieLe = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $modifieLe;

    /**
     * @var Collection<int, PhotoVehicule>
     */
    #[ORM\OneToMany(targetEntity: PhotoVehicule::class, mappedBy: 'vehicule', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $photos;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->modifieLe = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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

    public function getMarque(): string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getMisEnCirculationLe(): \DateTimeImmutable
    {
        return $this->misEnCirculationLe;
    }

    public function setMisEnCirculationLe(\DateTimeImmutable $misEnCirculationLe): static
    {
        $this->misEnCirculationLe = $misEnCirculationLe;

        return $this;
    }

    public function getKilometrage(): int
    {
        return $this->kilometrage;
    }

    public function setKilometrage(int $kilometrage): static
    {
        $this->kilometrage = $kilometrage;

        return $this;
    }

    public function getEnergie(): Energie
    {
        return $this->energie;
    }

    public function setEnergie(Energie $energie): static
    {
        $this->energie = $energie;

        return $this;
    }

    public function getBoite(): BoiteVitesses
    {
        return $this->boite;
    }

    public function setBoite(BoiteVitesses $boite): static
    {
        $this->boite = $boite;

        return $this;
    }

    public function getPuissanceFiscale(): ?int
    {
        return $this->puissanceFiscale;
    }

    public function setPuissanceFiscale(?int $puissanceFiscale): static
    {
        $this->puissanceFiscale = $puissanceFiscale;

        return $this;
    }

    public function getPuissanceDin(): ?int
    {
        return $this->puissanceDin;
    }

    public function setPuissanceDin(?int $puissanceDin): static
    {
        $this->puissanceDin = $puissanceDin;

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatut(): StatutVehicule
    {
        return $this->statut;
    }

    public function setStatut(StatutVehicule $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getUrlAnnonceExterne(): ?string
    {
        return $this->urlAnnonceExterne;
    }

    public function setUrlAnnonceExterne(?string $urlAnnonceExterne): static
    {
        $this->urlAnnonceExterne = $urlAnnonceExterne;

        return $this;
    }

    public function getPublieLe(): ?\DateTimeImmutable
    {
        return $this->publieLe;
    }

    public function setPublieLe(?\DateTimeImmutable $publieLe): static
    {
        $this->publieLe = $publieLe;

        return $this;
    }

    public function getModifieLe(): \DateTimeImmutable
    {
        return $this->modifieLe;
    }

    public function setModifieLe(\DateTimeImmutable $modifieLe): static
    {
        $this->modifieLe = $modifieLe;

        return $this;
    }

    /**
     * @return Collection<int, PhotoVehicule>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    /**
     * @return PhotoVehicule[] triées par `ordre` croissant
     */
    public function getPhotosTriees(): array
    {
        $photos = $this->photos->toArray();
        usort($photos, static fn (PhotoVehicule $a, PhotoVehicule $b) => $a->getOrdre() <=> $b->getOrdre());

        return $photos;
    }

    public function addPhoto(PhotoVehicule $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setVehicule($this);
        }

        return $this;
    }

    public function removePhoto(PhotoVehicule $photo): static
    {
        // orphanRemoval: retirer la photo de la collection suffit à programmer sa suppression.
        $this->photos->removeElement($photo);

        return $this;
    }

    public function __toString(): string
    {
        return isset($this->reference, $this->titre) ? sprintf('%s — %s', $this->reference, $this->titre) : 'Nouveau véhicule';
    }
}
