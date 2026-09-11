<?php

declare(strict_types=1);

namespace App\Message;

final readonly class GenererVariantesPhotoVehiculeMessage
{
    public function __construct(public int $photoId)
    {
    }
}
