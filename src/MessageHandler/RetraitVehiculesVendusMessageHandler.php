<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\RetraitVehiculesVendusMessage;
use App\Service\Vehicule\PublicationVehicule;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RetraitVehiculesVendusMessageHandler
{
    public function __construct(private readonly PublicationVehicule $publicationVehicule)
    {
    }

    public function __invoke(RetraitVehiculesVendusMessage $message): void
    {
        $this->publicationVehicule->retirerVehiculesVendusExpires();
    }
}
