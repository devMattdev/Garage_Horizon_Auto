<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\PhotoVehicule;
use App\Message\GenererVariantesPhotoVehiculeMessage;
use App\Service\Vehicule\StockagePhotoVehicule;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postRemove)]
final readonly class PhotoVehiculeSubscriber
{
    public function __construct(
        private MessageBusInterface $bus,
        private StockagePhotoVehicule $stockage,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $photo = $args->getObject();
        if ($photo instanceof PhotoVehicule && null !== $photo->getId()) {
            $this->bus->dispatch(new GenererVariantesPhotoVehiculeMessage($photo->getId()));
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $photo = $args->getObject();
        if ($photo instanceof PhotoVehicule) {
            $this->stockage->supprimer($photo);
        }
    }
}
