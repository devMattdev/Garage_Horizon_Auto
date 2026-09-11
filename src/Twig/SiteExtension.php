<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Bandeau;
use App\Entity\ParametresSite;
use App\Repository\BandeauRepository;
use App\Repository\ParametresSiteRepository;
use App\Service\Horaires\CalculateurOuverture;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SiteExtension extends AbstractExtension
{
    public function __construct(
        private readonly ParametresSiteRepository $parametresSiteRepository,
        private readonly BandeauRepository $bandeauRepository,
        private readonly CalculateurOuverture $calculateurOuverture,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('parametres_site', $this->parametresSite(...)),
            new TwigFunction('bandeau_actif', $this->bandeauActif(...)),
            new TwigFunction('garage_ouvert', $this->garageOuvert(...)),
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
}
