<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Réserve un écran ou une action du back-office au rôle Gérant (back.md section 7.2 et 8).
 *
 * @extends Voter<string, mixed>
 */
final class GerantVoter extends Voter
{
    public const string RESERVE_GERANT = 'RESERVE_GERANT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::RESERVE_GERANT === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return \in_array('ROLE_ADMIN', $token->getRoleNames(), true);
    }
}
