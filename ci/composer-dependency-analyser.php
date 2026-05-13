<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

$config
    ->addPathToScan(__DIR__ . '/../src', isDev: false)
    ->addPathToScan(__DIR__ . '/../tests', isDev: true)
    ->ignoreERrorsOnPackages(['doctrine/dbal', 'eureka/component-database'], [ErrorType::DEV_DEPENDENCY_IN_PROD])
;


return $config;
