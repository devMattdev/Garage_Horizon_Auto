<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Bandeau;
use App\Entity\ParametresSite;
use App\Repository\BandeauRepository;
use App\Repository\ParametresSiteRepository;
use App\Service\Horaires\CalculateurOuverture;
use App\Service\Horaires\ResumeHoraires;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SiteExtension extends AbstractExtension
{
    public function __construct(
        private readonly ParametresSiteRepository $parametresSiteRepository,
        private readonly BandeauRepository $bandeauRepository,
        private readonly CalculateurOuverture $calculateurOuverture,
        private readonly ResumeHoraires $resumeHoraires,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('parametres_site', $this->parametresSite(...)),
            new TwigFunction('bandeau_actif', $this->bandeauActif(...)),
            new TwigFunction('garage_ouvert', $this->garageOuvert(...)),
            new TwigFunction('prochaine_ouverture', $this->prochaineOuverture(...)),
            new TwigFunction('telephone_uri', $this->telephoneUri(...)),
            new TwigFunction('horaires_resume', $this->horairesResume(...)),
            new TwigFunction('horaires_groupes', $this->horairesGroupes(...)),
        ];
    }

    public function parametresSite(): ParametresSite
    {
        return $this->parametresSiteRepository->getInstance();
    }

    public function bandeauActif(): ?Bandeau
    {
        return $this->bandeauRepository->trouverActifCourant(new \DateTimeImmutable());
    }

    public function garageOuvert(): bool
    {
        return $this->calculateurOuverture->estOuvert(new \DateTimeImmutable());
    }

    public function prochaineOuverture(): ?\DateTimeImmutable
    {
        return $this->calculateurOuverture->prochaineOuverture(new \DateTimeImmutable());
    }

    public function telephoneUri(string $telephone): string
    {
        return (string) preg_replace('/[^\d+]/', '', $telephone);
    }

    public function horairesResume(): ?string
    {
        return $this->resumeHoraires->resume();
    }

    /**
     * @return list<array{jours: string, horaires: string}>
     */
    public function horairesGroupes(): array
    {
        return $this->resumeHoraires->groupes();
    }
}
