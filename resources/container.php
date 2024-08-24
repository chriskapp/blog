<?php

use PSX\Framework\Dependency\Configurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = Configurator::services($container->services());

    $services->load('Chriskapp\\Blog\\Command\\', __DIR__ . '/../src/Command')
        ->public();

    $services->load('Chriskapp\\Blog\\Controller\\', __DIR__ . '/../src/Controller')
        ->public();

    $services->load('Chriskapp\\Blog\\Service\\', __DIR__ . '/../src/Service')
        ->public();

    $services->load('Chriskapp\\Blog\\Table\\', __DIR__ . '/../src/Table/*.php');

};
