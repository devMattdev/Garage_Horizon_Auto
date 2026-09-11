<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PurgeRgpdMessage;
use App\Service\Rgpd\PurgeDonneesPersonnelles;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PurgeRgpdMessageHandler
{
    public function __construct(private readonly PurgeDonneesPersonnelles $purgeDonneesPersonnelles)
    {
    }

    public function __invoke(PurgeRgpdMessage $message): void
    {
        $this->purgeDonneesPersonnelles->purger();
    }
}
