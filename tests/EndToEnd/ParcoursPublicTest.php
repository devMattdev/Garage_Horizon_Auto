<?php

declare(strict_types=1);

namespace App\Tests\EndToEnd;

use App\Entity\Demande;
use App\Entity\Vehicule;
use App\Enum\BoiteVitesses;
use App\Enum\Energie;
use App\Enum\StatutVehicule;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

final class ParcoursPublicTest extends PantherTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        static::ensureKernelShutdown();
        static::bootKernel();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \LogicException('Le gestionnaire d’entités Doctrine est indisponible.');
        }
        $this->entityManager = $entityManager;

        $metadonnees = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $outilSchema = new SchemaTool($this->entityManager);
        $outilSchema->dropSchema($metadonnees);
        $outilSchema->createSchema($metadonnees);
    }

    public function testParcoursDeDemandeDeRendezVous(): void
    {
        $client = $this->navigateur();
        $crawler = $client->request('GET', '/');
        $crawler = $client->click($crawler->selectLink('Prendre rendez-vous')->link());

        self::assertSelectorTextContains('h1', 'Nous contacter');
        $form = $crawler->selectButton('Envoyer ma demande')->form();
        $client->submit($form, [
            'demande[type]' => 'rdv',
            'demande[nom]' => 'Marie Martin',
            'demande[telephone]' => '06 12 34 56 78',
            'demande[creneauSouhaite]' => 'Mardi matin',
            'demande[message]' => 'Revision annuelle',
            'demande[cfTurnstileResponse]' => 'jeton-e2e',
        ]);

        self::assertSelectorTextContains('h1', 'Votre demande a bien été envoyée');
        $this->entityManager->clear();
        self::assertSame(1, $this->entityManager->getRepository(Demande::class)->count([]));

        $this->assertAucuneViolationAccessibilite($client);
    }

    public function testConsultationDUnVehiculePublie(): void
    {
        $vehicule = (new Vehicule())
            ->setReference('E2E-001')
            ->setTitre('Peugeot 208 Allure')
            ->setMarque('Peugeot')
            ->setModele('208')
            ->setMisEnCirculationLe(new \DateTimeImmutable('2022-04-15'))
            ->setKilometrage(32500)
            ->setEnergie(Energie::ESSENCE)
            ->setBoite(BoiteVitesses::MANUELLE)
            ->setPrix('15990.00')
            ->setDescription('Vehicule entretenu et disponible immediatement.')
            ->setStatut(StatutVehicule::PUBLIE)
            ->setPublieLe(new \DateTimeImmutable('-1 day'));
        $this->entityManager->persist($vehicule);
        $this->entityManager->flush();

        $client = $this->navigateur();
        $crawler = $client->request('GET', '/vehicules-occasion');

        self::assertSelectorTextContains('h1', "Véhicules d'occasion");
        self::assertSelectorTextContains('.card-title', 'Peugeot 208 Allure');
        $client->click($crawler->selectLink('Voir la fiche')->link());

        self::assertSelectorTextContains('h1', 'Peugeot 208 Allure');
        self::assertSelectorTextContains('main', '32 500 km');
        self::assertSelectorTextContains('main', '15 990 €');
        self::assertSelectorTextContains('main a.btn-primary', "Ce véhicule m'intéresse");

        $this->assertAucuneViolationAccessibilite($client);
    }

    private function navigateur(): Client
    {
        static::ensureKernelShutdown();

        return static::createPantherClient();
    }

    private function assertAucuneViolationAccessibilite(Client $client): void
    {
        $cheminAxe = dirname(__DIR__, 2).'/node_modules/axe-core/axe.min.js';
        $scriptAxe = file_get_contents($cheminAxe);
        if (false === $scriptAxe) {
            self::fail('axe-core est introuvable. Exécutez `npm ci`.');
        }

        $client->executeScript($scriptAxe);
        $violations = $client->executeAsyncScript(<<<'JS'
            const done = arguments[arguments.length - 1];
            axe.run(document, {
                runOnly: {
                    type: 'tag',
                    values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'],
                },
            }).then(results => done(results.violations)).catch(error => done([{
                id: 'axe-core-error',
                description: String(error),
            }]));
            JS);

        self::assertSame(
            [],
            $violations,
            json_encode($violations, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR),
        );
    }
}
