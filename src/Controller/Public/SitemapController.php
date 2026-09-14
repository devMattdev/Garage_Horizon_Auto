<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Service\Seo\GenerateurSitemap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap', methods: ['GET'])]
    public function __invoke(GenerateurSitemap $generateurSitemap): Response
    {
        return new Response($generateurSitemap->contenu(), Response::HTTP_OK, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
