<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\GenererVariantesPhotoVehiculeMessage;
use App\Repository\PhotoVehiculeRepository;
use App\Service\Vehicule\GenerateurVariantesPhoto;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GenererVariantesPhotoVehiculeMessageHandler
{
    public function __construct(
        private PhotoVehiculeRepository $photos,
        private GenerateurVariantesPhoto $generateur,
    ) {
    }

    public function __invoke(GenererVariantesPhotoVehiculeMessage $message): void
    {
        $photo = $this->photos->find($message->photoId);
        if (null !== $photo) {
            $this->generateur->generer($photo);
        }
    }
}
