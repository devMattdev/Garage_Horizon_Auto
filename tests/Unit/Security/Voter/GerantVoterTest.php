<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Security\Voter\GerantVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Réserve les écrans du back-office marqués RESERVE_GERANT au rôle ROLE_ADMIN (back.md section 7.2, 8).
 */
final class GerantVoterTest extends TestCase
{
    /**
     * @param list<string> $roles
     */
    private function creerToken(array $roles): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getRoleNames')->willReturn($roles);

        return $token;
    }

    public function testAccordeLAccesAUnGerant(): void
    {
        $voter = new GerantVoter();

        $decision = $voter->vote($this->creerToken(['ROLE_ADMIN', 'ROLE_USER']), null, [GerantVoter::RESERVE_GERANT]);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $decision);
    }

    public function testRefuseLAccesAUnSecretariat(): void
    {
        $voter = new GerantVoter();

        $decision = $voter->vote($this->creerToken(['ROLE_SECRETARIAT', 'ROLE_USER']), null, [GerantVoter::RESERVE_GERANT]);

        self::assertSame(VoterInterface::ACCESS_DENIED, $decision);
    }

    public function testSAbstientSurUnAttributInconnu(): void
    {
        $voter = new GerantVoter();

        $decision = $voter->vote($this->creerToken(['ROLE_ADMIN']), null, ['ROLE_ADMIN']);

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $decision);
    }
}
