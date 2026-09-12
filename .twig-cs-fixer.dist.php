<?php

declare(strict_types=1);

use TwigCsFixer\Config\Config;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Standard\Symfony;

$ruleset = new Ruleset();
$ruleset->addStandard(new Symfony());

$config = new Config();
$config->setRuleset($ruleset);
$config->getFinder()->in(__DIR__.'/templates');

return $config;
