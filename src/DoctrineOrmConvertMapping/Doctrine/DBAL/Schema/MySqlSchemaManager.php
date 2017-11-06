<?php
namespace DoctrineOrmConvertMapping\Doctrine\DBAL\Schema;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\MySqlSchemaManager as MySqlSchemaManagerCore;

class MySqlSchemaManager extends MySqlSchemaManagerCore
{

    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $tableColumn = array_change_key_case($tableColumn, CASE_LOWER);

        $dbType = strtolower($tableColumn['type']);
        $dbType = strtok($dbType, '(), ');
        if (isset($tableColumn['length'])) {
            $length = $tableColumn['length'];
        } else {
            $length = strtok('(), ');
        }

        $fixed = null;

        if (!isset($tableColumn['name'])) {
            $tableColumn['name'] = '';
        }

        $scale = null;
        $precision = null;

        $type = $this->_platform->getDoctrineTypeMapping($dbType);

        // In cases where not connected to a database DESCRIBE $table does not return 'Comment'
        if (isset($tableColumn['comment'])) {
            $type = $this->extractDoctrineTypeFromComment($tableColumn['comment'], $type);
            $tableColumn['comment'] = $this->removeDoctrineTypeFromComment($tableColumn['comment'], $type);
        }

        switch ($dbType) {
            case 'char':
            case 'binary':
                $fixed = true;
                break;
            case 'float':
            case 'double':
            case 'real':
            case 'numeric':
            case 'decimal':
                if (preg_match('([A-Za-z]+\(([0-9]+)\,([0-9]+)\))', $tableColumn['type'], $match)) {
                    $precision = $match[1];
                    $scale = $match[2];
                    $length = null;
                }
                break;
            case 'tinytext':
                $length = MySqlPlatform::LENGTH_LIMIT_TINYTEXT;
                break;
            case 'text':
                $length = MySqlPlatform::LENGTH_LIMIT_TEXT;
                break;
            case 'mediumtext':
                $length = MySqlPlatform::LENGTH_LIMIT_MEDIUMTEXT;
                break;
            case 'tinyblob':
                $length = MySqlPlatform::LENGTH_LIMIT_TINYBLOB;
                break;
            case 'blob':
                $length = MySqlPlatform::LENGTH_LIMIT_BLOB;
                break;
            case 'mediumblob':
                $length = MySqlPlatform::LENGTH_LIMIT_MEDIUMBLOB;
                break;
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'integer':
            case 'bigint':
            case 'year':
                $length = null;
                break;
        }

        $length = ((int) $length == 0) ? null : (int) $length;

        $options = array(
            'length' => $length,
            'unsigned' => (bool) (strpos($tableColumn['type'], 'unsigned') !== false),
            'fixed' => (bool) $fixed,
            'default' => isset($tableColumn['default']) ? $tableColumn['default'] : null,
            'notnull' => (bool) ($tableColumn['null'] != 'YES'),
            'scale' => null,
            'precision' => null,
            'autoincrement' => (bool) (strpos($tableColumn['extra'], 'auto_increment') !== false),
            'comment' => isset($tableColumn['comment']) && $tableColumn['comment'] !== '' ? $tableColumn['comment'] : null,
        );

        if ($scale !== null && $precision !== null) {
            $options['scale'] = $scale;
            $options['precision'] = $precision;
        }

        try {
            $column = new Column($tableColumn['field'], Type::getType($type), $options);

            if (isset($tableColumn['collation'])) {
                $column->setPlatformOption('collation', $tableColumn['collation']);
            }

            return $column;
        } catch (\Exception $exc) {
            
        }
    }
}
