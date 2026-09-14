<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\FamillePrestation;
use App\Repository\PrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PrestationRepository::class)]
#[ORM\Table(name: 'prestation')]
#[ORM\UniqueConstraint(name: 'uniq_prestation_slug', fields: ['slug'])]
class Prestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Collection<int, PrestationEtape> */
    #[ORM\OneToMany(targetEntity: PrestationEtape::class, mappedBy: 'prestation', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $etapes;

    #[ORM\Column(length: 160)]
    private string $slug;

    #[ORM\Column(length: 160)]
    private string $nom;

    #[ORM\Column(enumType: FamillePrestation::class)]
    private FamillePrestation $famille;

    #[ORM\Column(length: 280)]
    private string $resume;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Le tarif indicatif ne peut être négatif ni nul (RG-07).')]
    private ?string $tarifIndicatif = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mentionTarif = null;

    #[ORM\Column]
    private bool $miseEnAvant = false;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $seoTitre = null;

    #[ORM\Column(length: 320, nullable: true)]
    private ?string $seoDescription = null;

    public function __construct()
    {
        $this->etapes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function getFamille(): FamillePrestation
    {
        return $this->famille;
    }

    public function setFamille(FamillePrestation $famille): static
    {
        $this->famille = $famille;

        return $this;
    }

    public function getResume(): string
    {
        return $this->resume;
    }

    public function setResume(string $resume): static
    {
        $this->resume = $resume;

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

    public function getTarifIndicatif(): ?string
    {
        return $this->tarifIndicatif;
    }

    public function setTarifIndicatif(?string $tarifIndicatif): static
    {
        $this->tarifIndicatif = $tarifIndicatif;

        return $this;
    }

    public function getMentionTarif(): ?string
    {
        return $this->mentionTarif;
    }

    public function setMentionTarif(?string $mentionTarif): static
    {
        $this->mentionTarif = $mentionTarif;

        return $this;
    }

    public function isMiseEnAvant(): bool
    {
        return $this->miseEnAvant;
    }

    public function setMiseEnAvant(bool $miseEnAvant): static
    {
        $this->miseEnAvant = $miseEnAvant;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getSeoTitre(): ?string
    {
        return $this->seoTitre;
    }

    public function setSeoTitre(?string $seoTitre): static
    {
        $this->seoTitre = $seoTitre;

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): static
    {
        $this->seoDescription = $seoDescription;

        return $this;
    }

    /**
     * @return Collection<int, PrestationEtape>
     */
    public function getEtapes(): Collection
    {
        return $this->etapes;
    }

    /**
     * @return PrestationEtape[] triées par `ordre` croissant
     */
    public function getEtapesTriees(): array
    {
        $etapes = $this->etapes->toArray();
        usort($etapes, static fn (PrestationEtape $a, PrestationEtape $b) => $a->getOrdre() <=> $b->getOrdre());

        return $etapes;
    }

    public function addEtape(PrestationEtape $etape): static
    {
        if (!$this->etapes->contains($etape)) {
            $this->etapes->add($etape);
            $etape->setPrestation($this);
        }

        return $this;
    }

    public function removeEtape(PrestationEtape $etape): static
    {
        // orphanRemoval : retirer l'étape de la collection suffit à programmer sa suppression.
        $this->etapes->removeElement($etape);

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? 'Prestation';
    }
}
