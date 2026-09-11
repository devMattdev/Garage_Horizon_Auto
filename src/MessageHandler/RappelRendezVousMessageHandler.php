<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\RappelRendezVousMessage;
use App\Service\Contact\RappelRendezVous;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RappelRendezVousMessageHandler
{
    public function __construct(private readonly RappelRendezVous $rappelRendezVous)
    {
    }

    public function __invoke(RappelRendezVousMessage $message): void
    {
        $this->rappelRendezVous->envoyerRappels();
    }
}
