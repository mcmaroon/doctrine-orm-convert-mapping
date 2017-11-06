<?php
namespace DoctrineOrmConvertMapping\Doctrine\DBAL\Platforms;

use Doctrine\DBAL\Platforms\MySqlPlatform as MySqlPlatformCore;

class MySqlPlatform extends MySqlPlatformCore {
    
    public final function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
    
    public function getDoctrineTypeMapping($dbType)
    {
        if ($this->doctrineTypeMapping === null) {
            $this->invokeMethod($this, 'initializeAllDoctrineTypeMappings');
        }

        $dbType = strtolower($dbType);

        if (!isset($this->doctrineTypeMapping[$dbType])) {
            $this->doctrineTypeMapping[$dbType] = 'text';
        }

        return $this->doctrineTypeMapping[$dbType];
    }
}