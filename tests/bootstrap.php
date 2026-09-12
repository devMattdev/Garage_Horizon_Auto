<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$repertoirePilotes = dirname(__DIR__).'/drivers';
if (is_dir($repertoirePilotes)) {
    $path = $repertoirePilotes.\PATH_SEPARATOR.(getenv('PATH') ?: '');
    putenv('PATH='.$path);
    $_SERVER['PATH'] = $path;
}

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
