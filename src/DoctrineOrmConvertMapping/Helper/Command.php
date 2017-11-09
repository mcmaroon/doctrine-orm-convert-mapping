<?php
namespace DoctrineOrmConvertMapping\Helper;

use Symfony\Component\Console\Command\Command as CommandCore;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

abstract class Command extends CommandCore
{
    
    const DEFAULT_DEST_PATH = 'mapping';
    
    protected function getEntityManager($dbParams)
    {
        $config = Setup::createAnnotationMetadataConfiguration(["/src/Entity"], true);
        return EntityManager::create($dbParams, $config);
    }
}
