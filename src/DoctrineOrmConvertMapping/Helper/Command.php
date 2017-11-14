<?php
namespace DoctrineOrmConvertMapping\Helper;

use Symfony\Component\Console\Command\Command as CommandCore;
use Symfony\Component\Console\Input\InputInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
// ~
use DoctrineOrmConvertMapping\Doctrine\DBAL\Platforms\MySqlPlatform;

abstract class Command extends CommandCore
{

    const DEFAULT_DEST_PATH = 'mapping/';
    const DEFAULT_SCHEMA_FILE_NAME = 'schema';
    const DEFAULT_SCHEMA_FILE_EXT = '.sql';
    const SCHEMA_TYPE_CREATE = 'create';
    const SCHEMA_TYPE_UPDATE = 'update';
    const SCHEMA_TYPE_DROP = 'drop';

    protected function createDestPath($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
        $realPath = realpath($path);

        $this->checkDestPath($realPath);

        return $realPath;
    }

    protected function checkDestPath($path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(
            sprintf("Destination directory '%s' does not exist.", $path)
            );
        }

        if (!is_writable($path)) {
            throw new \InvalidArgumentException(
            sprintf("Destination directory '%s' does not have write permissions.", $path)
            );
        }
    }

    protected function getConnectionParams(InputInterface $input)
    {
        return [
            'platform' => new MySqlPlatform(),
            'driver' => 'pdo_mysql',
            'dbname' => $input->getArgument('dbname'),
            'user' => $input->getArgument('dbuser'),
            'password' => $input->getArgument('dbpass')
        ];
    }

    protected function getAnnotationMetadataConfiguration($path)
    {
        return Setup::createAnnotationMetadataConfiguration([$path], true);
    }

    protected function getEntityManager(array $dbParams, \Doctrine\ORM\Configuration $config)
    {
        return EntityManager::create($dbParams, $config);
    }
}
