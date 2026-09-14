<?php

declare(strict_types=1);

namespace App\Service\Seo;

use App\Repository\PageLocaleRepository;
use App\Repository\PrestationRepository;
use App\Repository\VehiculeRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Régénère le sitemap.xml (back.md section 11, tâche planifiée quotidienne).
 */
class GenerateurSitemap
{
    public function __construct(
        private readonly PrestationRepository $prestationRepository,
        private readonly VehiculeRepository $vehiculeRepository,
        private readonly PageLocaleRepository $pageLocaleRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $cheminPublic,
    ) {
    }

    public function generer(): string
    {
        $chemin = rtrim($this->cheminPublic, '/').'/sitemap.xml';
        $xml = $this->construireXml();
        $xml->asXML($chemin);

        return $chemin;
    }

    public function contenu(): string
    {
        $contenu = $this->construireXml()->asXML();
        if (false === $contenu) {
            throw new \RuntimeException('Impossible de générer le sitemap XML.');
        }

        return $contenu;
    }

    private function construireXml(): \SimpleXMLElement
    {
        $urls = [
            $this->urlGenerator->generate('accueil', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('prestations_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('vehicules_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('faq', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('contact', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('garage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('professionnels', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('plan_site', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('mentions_legales', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->urlGenerator->generate('politique_confidentialite', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        foreach ($this->prestationRepository->findActivesOrdonnees() as $prestation) {
            $urls[] = $this->urlGenerator->generate('prestation_detail', ['slug' => $prestation->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        foreach ($this->vehiculeRepository->findPublies() as $vehicule) {
            $urls[] = $this->urlGenerator->generate('vehicule_detail', ['reference' => $vehicule->getReference()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        foreach ($this->pageLocaleRepository->findAll() as $pageLocale) {
            if (!$pageLocale->isActif()) {
                continue;
            }
            $urls[] = $this->urlGenerator->generate('page_locale_detail', ['commune' => $pageLocale->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        foreach (array_unique($urls) as $url) {
            $entree = $xml->addChild('url');
            $entree->addChild('loc', htmlspecialchars($url, \ENT_XML1));
        }

        return $xml;
    }
}
