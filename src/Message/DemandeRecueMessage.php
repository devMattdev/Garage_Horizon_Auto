<?php

declare(strict_types=1);

namespace App\Message;

final class DemandeRecueMessage
{
    public function __construct(public readonly int $demandeId)
    {
    }
}
