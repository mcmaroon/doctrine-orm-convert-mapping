<?php
namespace DoctrineOrmConvertMapping\Helper\DoctrineExtensions;

use \Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs;

class IgnoreEntityListener
{

    public function postGenerateSchemaTable(GenerateSchemaTableEventArgs $eventArgs)
    {
        $rc = $eventArgs->getClassMetadata()->getReflectionClass();
        if ($rc->implementsInterface(IgnoreEntityInterface::class)) {
            $entityTableName = $eventArgs->getClassMetadata()->getTableName();
            $eventArgs->getSchema()->dropTable($entityTableName);
        }
    }
}
