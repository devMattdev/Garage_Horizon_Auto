<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Demande;
use App\Message\DemandeRecueMessage;
use App\Service\Antiabus\TurnstileValidator;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class ContactControllerTest extends DatabaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->setServerParameter('REMOTE_ADDR', '2001:db8::'.bin2hex(random_bytes(8)));
    }

    public function testAfficheLeFormulaire(): void
    {
        $crawler = $this->client->request('GET', '/rendez-vous');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Prendre rendez-vous', $crawler->filterXPath('//h1')->text());
        self::assertCount(1, $crawler->filterXPath('//input[@name="demande[_token]"]'));
        self::assertCount(1, $crawler->filterXPath('//input[@name="demande[siteWeb]"]'));
    }

    public function testRefuseUneDemandeSansCoordonnee(): void
    {
        $crawler = $this->client->request('GET', '/rendez-vous');
        $form = $crawler->selectButton('Envoyer ma demande')->form([
            'demande[type]' => 'question',
            'demande[nom]' => 'Jean Dupont',
            'demande[message]' => 'Une question',
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('téléphone ou un courriel', (string) $this->client->getResponse()->getContent());
        self::assertSame(0, $this->entityManager->getRepository(Demande::class)->count([]));
    }

    public function testEnregistreEtMetEnFileUneDemandeValide(): void
    {
        $this->client->disableReboot();
        $turnstile = $this->createMock(TurnstileValidator::class);
        $turnstile->expects(self::once())->method('estValide')->willReturn(true);
        static::getContainer()->set(TurnstileValidator::class, $turnstile);

        $crawler = $this->client->request('GET', '/rendez-vous');
        $form = $crawler->selectButton('Envoyer ma demande')->form([
            'demande[type]' => 'devis',
            'demande[nom]' => 'Jean Dupont',
            'demande[email]' => 'jean@example.test',
            'demande[immatriculation]' => 'ab 123 cd',
            'demande[message]' => 'Demande de devis',
            'demande[cfTurnstileResponse]' => 'jeton-test',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/rendez-vous/confirmation');
        $demandes = $this->entityManager->getRepository(Demande::class)->findAll();
        self::assertCount(1, $demandes);
        self::assertSame('AB123CD', $demandes[0]->getImmatriculation());

        $transport = static::getContainer()->get('messenger.transport.async');
        self::assertInstanceOf(InMemoryTransport::class, $transport);
        $messages = $transport->getSent();
        self::assertCount(1, $messages);
        self::assertInstanceOf(DemandeRecueMessage::class, $messages[0]->getMessage());
    }

    public function testHoneypotIgnoreSilencieusementLaSoumission(): void
    {
        $this->client->disableReboot();
        $turnstile = $this->createMock(TurnstileValidator::class);
        $turnstile->expects(self::never())->method('estValide');
        static::getContainer()->set(TurnstileValidator::class, $turnstile);

        $crawler = $this->client->request('GET', '/rendez-vous');
        $form = $crawler->selectButton('Envoyer ma demande')->form([
            'demande[type]' => 'question',
            'demande[nom]' => 'Robot',
            'demande[email]' => 'robot@example.test',
            'demande[siteWeb]' => 'https://spam.example',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/rendez-vous/confirmation');
        self::assertSame(0, $this->entityManager->getRepository(Demande::class)->count([]));
    }

    public function testDeduplicationNePersisteEtNeNotifieQuUneFois(): void
    {
        $this->client->disableReboot();
        $turnstile = $this->createMock(TurnstileValidator::class);
        $turnstile->expects(self::exactly(2))->method('estValide')->willReturn(true);
        static::getContainer()->set(TurnstileValidator::class, $turnstile);

        for ($tentative = 0; $tentative < 2; ++$tentative) {
            $crawler = $this->client->request('GET', '/rendez-vous');
            $form = $crawler->selectButton('Envoyer ma demande')->form([
                'demande[type]' => 'question',
                'demande[nom]' => 'Jean Dupont',
                'demande[email]' => 'doublon@example.test',
                'demande[message]' => 'Message identique',
                'demande[cfTurnstileResponse]' => 'jeton-test',
            ]);
            $this->client->submit($form);
            self::assertResponseRedirects('/rendez-vous/confirmation');

            $transport = static::getContainer()->get('messenger.transport.async');
            self::assertInstanceOf(InMemoryTransport::class, $transport);
            self::assertCount(0 === $tentative ? 1 : 0, $transport->getSent());
        }

        self::assertSame(1, $this->entityManager->getRepository(Demande::class)->count([]));
    }

    public function testBloqueLaSixiemeSoumissionDeLaMemeAdresseIp(): void
    {
        $this->client->disableReboot();
        $turnstile = $this->createMock(TurnstileValidator::class);
        $turnstile->expects(self::exactly(5))->method('estValide')->willReturn(true);
        static::getContainer()->set(TurnstileValidator::class, $turnstile);

        for ($tentative = 1; $tentative <= 6; ++$tentative) {
            $crawler = $this->client->request('GET', '/rendez-vous');
            $form = $crawler->selectButton('Envoyer ma demande')->form([
                'demande[type]' => 'question',
                'demande[nom]' => 'Visiteur '.$tentative,
                'demande[email]' => sprintf('quota-%d@example.test', $tentative),
                'demande[message]' => 'Message '.$tentative,
                'demande[cfTurnstileResponse]' => 'jeton-test',
            ]);
            $this->client->submit($form);

            if ($tentative <= 5) {
                self::assertResponseRedirects('/rendez-vous/confirmation');
            }
        }

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString(
            'Trop de demandes envoyées depuis cette adresse',
            (string) $this->client->getResponse()->getContent(),
        );
        self::assertSame(5, $this->entityManager->getRepository(Demande::class)->count([]));
    }
}
