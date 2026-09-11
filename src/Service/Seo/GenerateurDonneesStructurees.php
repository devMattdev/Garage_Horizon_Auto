<?php

declare(strict_types=1);

namespace App\Service\Seo;

use App\Entity\ParametresSite;
use App\Entity\Prestation;
use App\Entity\QuestionFrequente;
use App\Entity\Vehicule;
use App\Repository\ParametresSiteRepository;

/**
 * Produit le JSON-LD AutoRepair, Service, FAQPage, Vehicle (back.md section 9 et 23).
 */
class GenerateurDonneesStructurees
{
    public function __construct(private readonly ParametresSiteRepository $parametresSiteRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function autoRepair(): array
    {
        $parametres = $this->parametresSiteRepository->getInstance();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'AutoRepair',
            'name' => $parametres->getRaisonSociale(),
            'address' => $this->adresse($parametres),
            'telephone' => $parametres->getTelephone(),
            'email' => $parametres->getEmail(),
            'geo' => null !== $parametres->getLatitude() && null !== $parametres->getLongitude() ? [
                '@type' => 'GeoCoordinates',
                'latitude' => $parametres->getLatitude(),
                'longitude' => $parametres->getLongitude(),
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function service(Prestation $prestation): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $prestation->getNom(),
            'description' => $prestation->getResume(),
            'provider' => [
                '@type' => 'AutoRepair',
                'name' => $this->parametresSiteRepository->getInstance()->getRaisonSociale(),
            ],
        ];
    }

    /**
     * @param iterable<QuestionFrequente> $questions
     *
     * @return array<string, mixed>
     */
    public function faqPage(iterable $questions): array
    {
        $entites = [];
        foreach ($questions as $question) {
            $entites[] = [
                '@type' => 'Question',
                'name' => $question->getQuestion(),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $question->getReponse(),
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entites,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function vehicle(Vehicule $vehicule): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Vehicle',
            'name' => $vehicule->getTitre(),
            'brand' => $vehicule->getMarque(),
            'model' => $vehicule->getModele(),
            'vehicleModelDate' => $vehicule->getMisEnCirculationLe()->format('Y'),
            'mileageFromOdometer' => [
                '@type' => 'QuantitativeValue',
                'value' => $vehicule->getKilometrage(),
                'unitCode' => 'KMT',
            ],
            'offers' => null !== $vehicule->getPrix() ? [
                '@type' => 'Offer',
                'price' => $vehicule->getPrix(),
                'priceCurrency' => 'EUR',
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function adresse(ParametresSite $parametres): array
    {
        return [
            '@type' => 'PostalAddress',
            'streetAddress' => $parametres->getAdresse(),
            'postalCode' => $parametres->getCodePostal(),
            'addressLocality' => $parametres->getVille(),
            'addressCountry' => 'FR',
        ];
    }
}
