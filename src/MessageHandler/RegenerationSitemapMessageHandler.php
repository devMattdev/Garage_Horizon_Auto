<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\RegenerationSitemapMessage;
use App\Service\Seo\GenerateurSitemap;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RegenerationSitemapMessageHandler
{
    public function __construct(private readonly GenerateurSitemap $generateurSitemap)
    {
    }

    public function __invoke(RegenerationSitemapMessage $message): void
    {
        $this->generateurSitemap->generer();
    }
}
