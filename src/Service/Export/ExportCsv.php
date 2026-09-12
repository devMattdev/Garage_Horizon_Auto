<?php

declare(strict_types=1);

namespace App\Service\Export;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Génère un export CSV (UTF-8, séparateur ";") depuis le back-office (back.md section 15).
 */
final class ExportCsv
{
    /**
     * @param string[]               $entetes
     * @param iterable<list<string>> $lignes
     */
    public function creerReponse(string $nomFichier, array $entetes, iterable $lignes): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($entetes, $lignes): void {
            $flux = fopen('php://output', 'w');
            if (false === $flux) {
                throw new \RuntimeException('Impossible d’ouvrir le flux de sortie CSV.');
            }

            // BOM UTF-8 pour une ouverture correcte dans Excel.
            fwrite($flux, "\xEF\xBB\xBF");
            fputcsv($flux, $entetes, ';');

            foreach ($lignes as $ligne) {
                fputcsv($flux, $ligne, ';');
            }

            fclose($flux);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $nomFichier),
        );

        return $response;
    }
}
