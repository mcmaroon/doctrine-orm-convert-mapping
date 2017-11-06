<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use DoctrineOrmConvertMapping\Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;

$application = new Application();

$application->add(new ConvertMappingCommand());

$application->run();
