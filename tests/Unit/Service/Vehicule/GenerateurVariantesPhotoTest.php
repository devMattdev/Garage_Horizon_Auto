<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Vehicule;

use App\Entity\PhotoVehicule;
use App\Service\Vehicule\GenerateurVariantesPhoto;
use App\Service\Vehicule\StockagePhotoVehicule;
use PHPUnit\Framework\TestCase;

final class GenerateurVariantesPhotoTest extends TestCase
{
    private string $repertoire;

    protected function setUp(): void
    {
        $this->repertoire = sys_get_temp_dir().'/horizon-photo-test-'.bin2hex(random_bytes(6));
        self::assertTrue(mkdir($this->repertoire.'/source', 0775, true));
    }

    protected function tearDown(): void
    {
        foreach (['vignette', 'carte', 'detail', 'source'] as $dossier) {
            foreach (glob($this->repertoire.'/'.$dossier.'/*') ?: [] as $fichier) {
                unlink($fichier);
            }
            if (is_dir($this->repertoire.'/'.$dossier)) {
                rmdir($this->repertoire.'/'.$dossier);
            }
        }
        if (is_dir($this->repertoire)) {
            rmdir($this->repertoire);
        }
    }

    public function testGenereLesTroisTaillesDansLesDeuxFormatsSansAgrandirLaSource(): void
    {
        $photo = (new PhotoVehicule())->setFichier('photo-source.png')->setTexteAlternatif('Vue avant');
        $source = imagecreatetruecolor(1000, 500);
        self::assertInstanceOf(\GdImage::class, $source);
        self::assertTrue(imagepng($source, $this->repertoire.'/source/photo-source.png'));
        $stockage = new StockagePhotoVehicule($this->repertoire);
        (new GenerateurVariantesPhoto($stockage))->generer($photo);

        foreach (['vignette' => 400, 'carte' => 800, 'detail' => 1000] as $variante => $largeur) {
            foreach (['webp', 'jpg'] as $format) {
                $chemin = $stockage->cheminVariante($photo, $variante, $format);
                self::assertFileExists($chemin);
                $dimensions = getimagesize($chemin);
                self::assertIsArray($dimensions);
                self::assertSame($largeur, $dimensions[0]);
                self::assertSame((int) ($largeur / 2), $dimensions[1]);
            }
        }
    }

    public function testRefuseUnNomDeFichierQuiSortDuStockage(): void
    {
        $photo = (new PhotoVehicule())->setFichier('../secret.jpg')->setTexteAlternatif('Vue');
        $stockage = new StockagePhotoVehicule($this->repertoire);

        $this->expectException(\InvalidArgumentException::class);
        $stockage->cheminSource($photo);
    }
}
