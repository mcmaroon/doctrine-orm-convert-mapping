<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use DoctrineOrmConvertMapping\Command;

$application = new Application();

$application->add(new Command\ConvertMappingCommand());
$application->add(new Command\SchemaCommand());
$application->add(new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand());
$application->add(new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand());
$application->add(new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand());

$application->run();
