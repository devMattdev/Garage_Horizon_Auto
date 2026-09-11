<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Contact;

use App\Entity\Demande;
use App\Enum\TypeDemande;
use App\Repository\DemandeRepository;
use App\Service\Contact\DemandeHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class DemandeHandlerTest extends TestCase
{
    private function creerDemande(TypeDemande $type = TypeDemande::QUESTION, ?string $immatriculation = null, ?string $email = 'client@example.com'): Demande
    {
        $demande = new Demande();
        $demande->setType($type);
        $demande->setNom('Client Test');
        $demande->setEmail($email);
        $demande->setImmatriculation($immatriculation);

        return $demande;
    }

    /**
     * RG-02 : l'immatriculation est normalisée en majuscules sans espaces.
     */
    public function testTraiterNormaliseImmatriculation(): void
    {
        $demande = $this->creerDemande(immatriculation: 'ab-123 cd');

        $demandeRepository = $this->createMock(DemandeRepository::class);
        $demandeRepository->method('trouverDoublonRecent')->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('persist')->willReturnCallback(function (Demande $d): void {
            // Simule l'attribution d'un identifiant par Doctrine lors du persist.
            (new \ReflectionProperty(Demande::class, 'id'))->setValue($d, 42);
        });

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->method('dispatch')->willReturn(new Envelope(new \stdClass(), [new HandledStamp('ok', 'handler')]));

        $handler = new DemandeHandler($entityManager, $demandeRepository, $messageBus, 12);

        $resultat = $handler->traiter($demande);

        self::assertSame('AB-123CD', $resultat['demande']->getImmatriculation());
        self::assertFalse($resultat['doublon']);
    }

    /**
     * RG-09 : purgePrevueLe est calculé à partir de la durée de conservation paramétrée.
     */
    public function testTraiterCalculeLaDatePurge(): void
    {
        $demande = $this->creerDemande();

        $demandeRepository = $this->createMock(DemandeRepository::class);
        $demandeRepository->method('trouverDoublonRecent')->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('persist')->willReturnCallback(function (Demande $d): void {
            (new \ReflectionProperty(Demande::class, 'id'))->setValue($d, 1);
        });

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->method('dispatch')->willReturn(new Envelope(new \stdClass(), [new HandledStamp('ok', 'handler')]));

        $handler = new DemandeHandler($entityManager, $demandeRepository, $messageBus, 12);

        $avant = new \DateTimeImmutable('+12 months');
        $resultat = $handler->traiter($demande);
        $apres = new \DateTimeImmutable('+12 months');

        self::assertGreaterThanOrEqual($avant->format('Y-m-d'), $resultat['demande']->getPurgePrevueLe()->format('Y-m-d'));
        self::assertLessThanOrEqual($apres->format('Y-m-d'), $resultat['demande']->getPurgePrevueLe()->format('Y-m-d'));
    }

    /**
     * RG-12 : un doublon récent (même courriel, type et message) n'est pas re-persisté.
     */
    public function testTraiterDetecteUnDoublon(): void
    {
        $demande = $this->creerDemande();
        $doublonExistant = $this->creerDemande();

        $demandeRepository = $this->createMock(DemandeRepository::class);
        $demandeRepository->method('trouverDoublonRecent')->willReturn($doublonExistant);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::never())->method('dispatch');

        $handler = new DemandeHandler($entityManager, $demandeRepository, $messageBus, 12);

        $resultat = $handler->traiter($demande);

        self::assertTrue($resultat['doublon']);
        self::assertSame($doublonExistant, $resultat['demande']);
    }
}
