<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Vehicule;
use Symfony\Contracts\EventDispatcher\Event;

final class VehiculeVenduEvent extends Event
{
    public function __construct(public readonly Vehicule $vehicule)
    {
    }
}
