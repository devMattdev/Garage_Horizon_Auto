<?php

declare(strict_types=1);

namespace App\Service\Vehicule;

use App\Entity\PhotoVehicule;

final class StockagePhotoVehicule
{
    public const VARIANTES = [
        'vignette' => 400,
        'carte' => 800,
        'detail' => 1600,
    ];

    public function __construct(private readonly string $repertoirePhotosVehicules)
    {
    }

    public function cheminSource(PhotoVehicule $photo): string
    {
        return $this->repertoirePhotosVehicules.'/source/'.$this->nomFichierSecurise($photo);
    }

    public function cheminVariante(PhotoVehicule $photo, string $variante, string $format): string
    {
        if (!isset(self::VARIANTES[$variante]) || !\in_array($format, ['webp', 'jpg'], true)) {
            throw new \InvalidArgumentException('Variante de photo inconnue.');
        }

        return $this->repertoirePhotosVehicules.'/'.$variante.'/'.$this->base($photo).'.'.$format;
    }

    public function supprimer(PhotoVehicule $photo): void
    {
        $chemins = [$this->cheminSource($photo)];
        foreach (array_keys(self::VARIANTES) as $variante) {
            $chemins[] = $this->cheminVariante($photo, $variante, 'webp');
            $chemins[] = $this->cheminVariante($photo, $variante, 'jpg');
        }

        foreach ($chemins as $chemin) {
            if (is_file($chemin) && !@unlink($chemin)) {
                throw new \RuntimeException(sprintf('Impossible de supprimer la photo « %s ».', $chemin));
            }
        }
    }

    private function nomFichierSecurise(PhotoVehicule $photo): string
    {
        $fichier = basename($photo->getFichier());
        if ($fichier !== $photo->getFichier()) {
            throw new \InvalidArgumentException('Nom de fichier de photo invalide.');
        }

        return $fichier;
    }

    private function base(PhotoVehicule $photo): string
    {
        return pathinfo($this->nomFichierSecurise($photo), PATHINFO_FILENAME);
    }
}
