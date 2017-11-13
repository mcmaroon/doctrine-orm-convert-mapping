<?php
$autoloadPath = isset($autoloadPath) ? $autoloadPath : __DIR__ . '/vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

use Symfony\Component\Console\Application;
use DoctrineOrmConvertMapping\Command;

$application = new Application();

$application->add(new Command\ConvertMappingCommand());
$application->add(new Command\SchemaCommand());
$application->add(new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand());
$application->add(new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand());
$application->add(new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand());
$application->add(new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand());

$application->run();
