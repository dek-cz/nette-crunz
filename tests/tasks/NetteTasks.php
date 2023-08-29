<?php
/**
 * this is an example of implementation
 */
$root = dirname(__DIR__, 2);
require $root. '/vendor/autoload.php';
use DekApps\Crunz\Crunz;
use Tests\DekApps\DiIncubator;

$configurator = new DiIncubator($root);
$configurator->setForceReloadContainer();
$configurator->addConfig(__DIR__ . '/../crunz.neon');
$container = $configurator->createContainer();
$crunz = $container->getByType(Crunz::class);

$crunz->run();

return $crunz->getService();