<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ParametresSiteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Enregistrement unique (id = 1) : coordonnées et réglages du garage.
 */
#[ORM\Entity(repositoryClass: ParametresSiteRepository::class)]
#[ORM\Table(name: 'parametres_site')]
class ParametresSite
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id = 1;

    #[ORM\Column(length: 180)]
    private string $raisonSociale = '';

    #[ORM\Column(length: 255)]
    private string $adresse = '';

    #[ORM\Column(length: 10)]
    private string $codePostal = '';

    #[ORM\Column(length: 120)]
    private string $ville = '';

    #[ORM\Column(length: 20)]
    private string $telephone = '';

    #[ORM\Column(length: 180)]
    private string $email = '';

    #[ORM\Column(length: 14)]
    private string $siret = '';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlFicheGoogle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlLeboncoin = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $numeroWhatsapp = null;

    #[ORM\Column]
    private int $delaiReponseJoursOuvres = 2;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRaisonSociale(): string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(string $raisonSociale): static
    {
        $this->raisonSociale = $raisonSociale;

        return $this;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostal(): string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSiret(): string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getUrlFicheGoogle(): ?string
    {
        return $this->urlFicheGoogle;
    }

    public function setUrlFicheGoogle(?string $urlFicheGoogle): static
    {
        $this->urlFicheGoogle = $urlFicheGoogle;

        return $this;
    }

    public function getUrlLeboncoin(): ?string
    {
        return $this->urlLeboncoin;
    }

    public function setUrlLeboncoin(?string $urlLeboncoin): static
    {
        $this->urlLeboncoin = $urlLeboncoin;

        return $this;
    }

    public function getNumeroWhatsapp(): ?string
    {
        return $this->numeroWhatsapp;
    }

    public function setNumeroWhatsapp(?string $numeroWhatsapp): static
    {
        $this->numeroWhatsapp = $numeroWhatsapp;

        return $this;
    }

    public function getDelaiReponseJoursOuvres(): int
    {
        return $this->delaiReponseJoursOuvres;
    }

    public function setDelaiReponseJoursOuvres(int $delaiReponseJoursOuvres): static
    {
        $this->delaiReponseJoursOuvres = $delaiReponseJoursOuvres;

        return $this;
    }
}
