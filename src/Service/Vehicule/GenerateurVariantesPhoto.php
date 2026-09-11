<?php

declare(strict_types=1);

namespace App\Service\Vehicule;

use App\Entity\PhotoVehicule;

final readonly class GenerateurVariantesPhoto
{
    private const TYPES_AUTORISES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(private StockagePhotoVehicule $stockage)
    {
    }

    public function generer(PhotoVehicule $photo): void
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('L’extension GD est requise.');
        }

        $source = $this->stockage->cheminSource($photo);
        $mime = is_file($source) ? (new \finfo(\FILEINFO_MIME_TYPE))->file($source) : false;
        if (!\is_string($mime) || !\in_array($mime, self::TYPES_AUTORISES, true)) {
            throw new \RuntimeException('Le fichier source n’est pas une image autorisée.');
        }

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png' => @imagecreatefrompng($source),
            'image/webp' => @imagecreatefromwebp($source),
        };
        if (!$image instanceof \GdImage) {
            throw new \RuntimeException('L’image source est illisible.');
        }

        foreach (StockagePhotoVehicule::VARIANTES as $nom => $largeur) {
            $this->creerVariante($image, $photo, $nom, $largeur);
        }
    }

    private function creerVariante(\GdImage $source, PhotoVehicule $photo, string $nom, int $largeurMax): void
    {
        if ($largeurMax < 1) {
            throw new \InvalidArgumentException('La largeur maximale doit être positive.');
        }
        $largeurSource = imagesx($source);
        $hauteurSource = imagesy($source);
        $largeur = $largeurSource < $largeurMax ? $largeurSource : $largeurMax;
        $hauteur = max(1, (int) round($hauteurSource * ($largeur / $largeurSource)));
        $cible = imagecreatetruecolor($largeur, $hauteur);
        if (!$cible instanceof \GdImage) {
            throw new \RuntimeException('Impossible d’allouer la variante image.');
        }

        $blanc = imagecolorallocate($cible, 255, 255, 255);
        if (false === $blanc) {
            throw new \RuntimeException('Impossible d’allouer la couleur de fond.');
        }
        imagefill($cible, 0, 0, $blanc);
        imagecopyresampled($cible, $source, 0, 0, 0, 0, $largeur, $hauteur, $largeurSource, $hauteurSource);
        foreach (['webp', 'jpg'] as $format) {
            $chemin = $this->stockage->cheminVariante($photo, $nom, $format);
            $dossier = dirname($chemin);
            if (!is_dir($dossier) && !mkdir($dossier, 0775, true) && !is_dir($dossier)) {
                throw new \RuntimeException('Impossible de créer le dossier des variantes.');
            }
            $ok = 'webp' === $format ? imagewebp($cible, $chemin, 82) : imagejpeg($cible, $chemin, 85);
            if (!$ok) {
                throw new \RuntimeException('Impossible d’écrire une variante image.');
            }
        }
    }
}
